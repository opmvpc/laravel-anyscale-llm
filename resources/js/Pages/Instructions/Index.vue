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
                <template #title> Informations Personnelles </template>

                <template #description>
                    Que voudriez-vous que l'assistant sache sur vous pour vous
                    fournir de meilleures réponses à vos questions ?
                </template>

                <template #form>
                    <div class="col-span-12">
                        <InputLabel for="personnal" value="Informations" />
                        <TextareaInput
                            rows="10"
                            id="personal"
                            v-model="formPersonal.personal"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Présentez vous ..."
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
                <template #title> Comportement </template>

                <template #description>
                    Comment voudriez-vous que l'assistant réagisse à vos
                    questions ? Donnez lui des instructions à respecter dans
                    toutes vos conversations.
                </template>

                <template #form>
                    <div class="col-span-12">
                        <InputLabel for="behavior" value="Instructions" />
                        <TextareaInput
                            rows="10"
                            id="behavior"
                            v-model="formBehavior.behavior"
                            type="text"
                            class="mt-1 block w-full"
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
    </AppLayout>
</template>
