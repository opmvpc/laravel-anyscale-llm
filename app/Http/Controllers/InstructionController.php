<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstructionController extends Controller
{
    public function index(Request $request)
    {
        $instruction = $request->user()->instruction;

        if (!$instruction) {
            $instruction = $request->user()->instruction()->create([
                'personal' => '',
                'behavior' => <<<'EOT'
                    - Tu est un modèle de language. Tu es un assistant dont l'utilisateur t'a donné le nom de Aria.
                    - Veilles à l'orthographe. Soigne ton style. Utilise des synonymes. Ne commence pas tes phrases par "en tant que modèle de language".
                    - Si besoin, demande des précisions. N'invente rien si je ne le demande pas explicitement.
                    EOT,
                'commands' => <<<'EOT'
                    - /aide donner de l’aide sur les commandes
                    - /recherche effectuer une recherche avec une query
                    - /idees donner une liste d’idées sur un sujet
                    - /reflexion mode réflexion : l’assistant découpera sa réponse en 2 parties. Une partie réflexion pour expliquer à haute voix son processus de pensée. Ensuite une partie Réponse, se nourrissant de ses propres réflexions pour générer une réponse de meilleure qualité.
                    - /ameliore amélioration du style : améliorer le texte précédent. Raccourcir les phrases, retravailler les tournures de phrases, organiser l’information efficacement avec des titres de différents niveaux, des listes et des tableaux. Le texte doit être agréable à lire.
                    EOT,
            ]);
        }

        return Inertia::render('Instructions/Index', [
            'instruction' => $instruction,
        ]);
    }

    public function update(Request $request)
    {
        $instruction = Auth::user()->instruction;

        if (null !== $request->input('personal')) {
            $request->validate([
                'personal' => ['nullable', 'string', 'max:1500'],
            ]);

            $instruction->update([
                'personal' => $request->input('personal'),
            ]);

            session()->flash('flash.banner', 'Vos informations personnelles ont été mises à jour.');
        } elseif (null !== $request->input('behavior')) {
            $request->validate([
                'behavior' => ['nullable', 'string', 'max:1500'],
            ]);

            $instruction->update([
                'behavior' => $request->input('behavior'),
            ]);

            session()->flash('flash.banner', 'Vos instructions comportementales ont été mises à jour.');
        } elseif (null !== $request->input('commands')) {
            $request->validate([
                'commands' => ['nullable', 'string', 'max:1500'],
            ]);

            $instruction->update([
                'commands' => $request->input('commands'),
            ]);

            session()->flash('flash.banner', 'Vos commandes ont été mises à jour.');
        }
    }
}
