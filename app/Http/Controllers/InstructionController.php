<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InstructionController extends Controller
{
    public function index()
    {
        $instruction = Auth::user()->instruction;

        if (!$instruction) {
            $instruction = Auth::user()->instruction()->create([
                'personal' => '',
                'behavior' => <<<'EOT'
                    - Tu est un modèle de language. Tu es un assistant dont l'utilisateur t'a donné le nom de Maman.
                    - Veilles à l'orthographe. Soigne ton style. Utilise des synonymes. Ne commence pas tes phrases par "en tant que modèle de language".
                    - Si besoin, demande des précisions. N'invente rien si je ne le demande pas explicitement.

                    ### Commands:
                    Here are the hotkeys that I will be using so remember the "Call to Action":

                    /save - restate the GOAL, summarize progress so far, and recommend a next step
                    /reason - share your reasoning for the response you generated
                    /settings - update the goal
                    /new - Forget previous input
                    /validate - Validate information from the internet.
                    /explore - Provide more creative or out-of-the-box ideas.
                    /commands - Lists all available commands for quick reference.
                    /help - Lists all available commands for quick reference and explains the purpose of each command with examples.

                    ### Rules:
                    1. End every output with a question or a recommended next step.
                    2. Do not answer with "As a large language model..." or "As an artificial intelligence..." I already know that.
                    3. Don’t assume any point. Always ask me to clarify the points you can assume.
                    4. Be excellent at reasoning. Always perform tree of thought and chain of thought technique before you answer.
                    5. Summarize key takeaways at the end of detailed.
                    EOT,
            ]);
        }

        return Inertia::render('Instructions/Index', [
            'instruction' => $instruction,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'personal' => ['missing_with:behavior', 'nullable', 'string', 'max:1500'],
            'behavior' => ['missing_with:personal', 'string', 'max:1500'],
        ]);

        $instruction = Auth::user()->instruction;

        if (null !== $request->input('personal')) {
            $instruction->update([
                'personal' => $request->input('personal'),
            ]);

            session()->flash('flash.banner', 'Vos informations personnelles ont été mises à jour.');
        } else {
            $instruction->update([
                'behavior' => $request->input('behavior'),
            ]);

            session()->flash('flash.banner', 'Vos instructions comportementales ont été mises à jour.');
        }
    }
}
