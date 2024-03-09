<script setup>
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextareaInput from "@/Components/TextareaInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    instruction: Object,
});

const formPersonal = useForm({
    _method: "POST",
    personal: props.instruction.personal,
});

const formBehavior = useForm({
    _method: "POST",
    behavior: props.instruction.behavior,
});

const formCommands = useForm({
    _method: "POST",
    commands: props.instruction.commands,
});

const sendPersonal = async () => {
    formPersonal.post(route("instructions.update"), {
        errorBag: "personal",
        preserveScroll: true,
        onSuccess: async () => {},
    });
};

const sendBehavior = async () => {
    formBehavior.post(route("instructions.update"), {
        errorBag: "behavior",
        preserveScroll: true,
        onSuccess: async () => {},
    });
};

const sendCommands = async () => {
    formCommands.post(route("instructions.update"), {
        errorBag: "commands",
        preserveScroll: true,
        onSuccess: async () => {},
    });
};
</script>

<template>
    <AppLayout title="Instructions Personnalisées">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Instructions Personnalisées
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="sendPersonal">
                <template #title> À propos de vous </template>

                <template #description>
                    <p>
                        Présentez-vous brièvement pour personnaliser
                        l'interaction avec votre assistant.
                    </p>

                    <div class="mt-3">
                        <div class="p-4 bg-indigo-100 rounded-lg">
                            <h3 class="text-base font-medium text-indigo-900">
                                Idées :
                            </h3>
                            <ul
                                class="text-sm text-indigo-800 mt-2 list-disc pl-5 space-y-1"
                            >
                                <li>Qui êtes-vous?</li>
                                <li>Votre domaine d'expertise</li>
                            </ul>
                            <p
                                class="mt-4 text-sm text-indigo-600 bg-indigo-50 p-3 rounded-md"
                            >
                                Exemple :
                                <span class="italic"
                                    >"Je suis prof de langue, intégrant des jeux
                                    de rôles et des technologies interactives
                                    dans mes cours, ce qui permet d'aborder la
                                    langue de manière dynamique et engageante,
                                    encouragent l'expression et la compréhension
                                    orale."</span
                                >
                            </p>
                        </div>
                    </div>
                </template>

                <template #form>
                    <div class="col-span-12 h-full">
                        <InputLabel for="personnal" value="Informations" />
                        <TextareaInput
                            id="personal"
                            v-model="formPersonal.personal"
                            type="text"
                            class="mt-1 block w-full h-[40vh]"
                            placeholder="Présentez vous ..."
                            autofocus
                        />
                        <InputError
                            :message="formPersonal.errors.personal"
                            class="mt-2"
                        />
                    </div>
                </template>

                <template #actions>
                    <ActionMessage
                        :on="formPersonal.recentlySuccessful"
                        class="me-3"
                    >
                        Enregistré.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{
                            'opacity-25': formPersonal.processing,
                        }"
                        :disabled="formPersonal.processing"
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="sendBehavior">
                <template #title> Comportement de l'assistant </template>

                <template #description>
                    <p>
                        Définissez comment vous souhaitez que l'assistant
                        interagisse avec vous. Cela inclut le ton, le niveau de
                        détail des réponses, et tout autre préférence qui rendra
                        l'utilisation de l'assistant plus naturelle et conforme
                        à vos attentes.
                    </p>

                    <div class="mt-3">
                        <div class="p-4 bg-indigo-100 rounded-lg">
                            <h3 class="text-base font-medium text-indigo-900">
                                Suggestions :
                            </h3>
                            <ul
                                class="text-sm text-indigo-800 mt-2 list-disc pl-5 space-y-1"
                            >
                                <li>
                                    Préférence de ton (amical, formel, etc.)
                                </li>
                                <li>
                                    Format des réponses (listes, paragraphes
                                    détaillés, etc.)
                                </li>
                                <li>
                                    Approches spécifiques pour expliquer des
                                    concepts
                                </li>
                            </ul>
                            <p
                                class="mt-4 text-sm text-indigo-600 bg-indigo-50 p-3 rounded-md"
                            >
                                Exemple :
                                <span class="italic"
                                    >"Je préfère un ton décontracté. Pour les
                                    sujets techniques, utilisez des exemples
                                    pratiques pour clarifier les concepts.
                                    J'aime les résumés suivis de détails si
                                    nécessaire."</span
                                >
                            </p>
                        </div>
                    </div>
                </template>

                <template #form>
                    <div class="col-span-12">
                        <InputLabel for="behavior" value="Instructions" />
                        <TextareaInput
                            id="behavior"
                            v-model="formBehavior.behavior"
                            type="text"
                            class="mt-1 block w-full h-[40vh]"
                        />
                        <InputError
                            :message="formBehavior.errors.behavior"
                            class="mt-2"
                        />
                    </div>
                </template>

                <template #actions>
                    <ActionMessage
                        :on="formBehavior.recentlySuccessful"
                        class="me-3"
                    >
                        Enregistré.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{
                            'opacity-25': formBehavior.processing,
                        }"
                        :disabled="formBehavior.processing"
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="sendCommands">
                <template #title> Créer des commandes personnalisées </template>

                <template #description>
                    <p>
                        Ici, vous pouvez définir vos propres commandes pour
                        interagir de manière unique avec votre assistant. Les
                        commandes vous permettent de simplifier les actions
                        récurrentes ou de personnaliser vos interactions.
                    </p>

                    <div class="mt-3">
                        <div class="p-4 bg-indigo-100 rounded-lg">
                            <h3 class="text-base font-medium text-indigo-900">
                                Comment ça marche ?
                            </h3>
                            <ul
                                class="text-sm text-indigo-800 mt-2 list-disc pl-5 space-y-1"
                            >
                                <li>
                                    Commencez chaque commande par '/' suivi du
                                    nom de la commande.
                                </li>
                                <li>
                                    Expliquez clairement l'action souhaitée
                                    après le nom de la commande.
                                </li>
                            </ul>
                            <p
                                class="mt-4 text-sm text-indigo-600 bg-indigo-50 p-3 rounded-md"
                            >
                                Exemple :
                                <span class="italic">
                                    Créez une commande "/resume" pour demander
                                    un résumé du texte précédent. Incluez dans
                                    la description : "Résume le texte fourni ou
                                    précédent en points clés."
                                </span>
                            </p>
                        </div>
                    </div>
                </template>

                <template #form>
                    <div class="col-span-12">
                        <InputLabel for="behavior" value="Commandes" />
                        <TextareaInput
                            id="behavior"
                            v-model="formCommands.commands"
                            type="text"
                            class="mt-1 block w-full h-[40vh]"
                        />
                        <InputError
                            :message="formCommands.errors.commands"
                            class="mt-2"
                        />
                    </div>
                </template>

                <template #actions>
                    <ActionMessage
                        :on="formCommands.recentlySuccessful"
                        class="me-3"
                    >
                        Enregistré.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{
                            'opacity-25': formCommands.processing,
                        }"
                        :disabled="formCommands.processing"
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>
    </AppLayout>
</template>
