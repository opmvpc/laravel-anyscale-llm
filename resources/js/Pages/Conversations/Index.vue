<script setup>
import DangerButton from "@/Components/DangerButton.vue";
import DialogModal from "@/Components/DialogModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteIcon from "@/Components/DeleteIcon.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import ConversationIcon from "@/Components/ConversationIcon.vue";

const props = defineProps({
    conversations: Array,
});

const formCreateConversation = useForm({
    _method: "POST",
});

const createConversation = () => {
    formCreateConversation.post(route("conversations.create"), {
        preserveScroll: true,
    });
};

const formDeleteConversation = useForm({
    _method: "DELETE",
});
const confirmingConversationDeletion = ref(false);
const ConversationIdToDelete = ref(null);

const confirmConversationDeletion = (id) => {
    ConversationIdToDelete.value = id;
    confirmingConversationDeletion.value = true;
};

const deleteConversation = () => {
    formDeleteConversation.delete(
        route("conversations.delete", ConversationIdToDelete.value),
        {
            preserveScroll: true,
            onSuccess: () => {
                confirmingConversationDeletion.value = false;
            },
            onError: () => {
                confirmingConversationDeletion.value = false;
            },
        }
    );
};

const closeModal = () => {
    confirmingConversationDeletion.value = false;
};
</script>

<template>
    <AppLayout title="Conversations">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Conversations
                </h2>

                <div class="">
                    <PrimaryButton
                        @click="createConversation"
                        :class="{
                            'opacity-25': formCreateConversation.processing,
                        }"
                        :disabled="formCreateConversation.processing"
                        title="Nouvelle conversation"
                    >
                        <ConversationIcon class="h-4 w-4" />
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <SectionTitle>
                    <template #title> Mes Conversations </template>
                    <template #description>
                        <p>
                            Vous pouvez créer un nouveau fil de discussion pour
                            discuter avec l'assistant.
                        </p>
                    </template>
                </SectionTitle>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <!-- Liste des liens vers les conversations -->
                    <ul
                        class="mt-5 md:mt-0 md:col-span-2 flex flex-col divide-y-2 rounded-xl overflow-hidden shadow"
                        v-if="conversations.length"
                    >
                        <li
                            v-for="conversation in conversations"
                            :key="conversation.id"
                            class="bg-white py-4 px-8 flex hover:bg-indigo-50 transition duration-150 items-center justify-between space-x-4"
                        >
                            <Link
                                :href="`/conversations/${conversation.id}`"
                                class="grow hover:text-indigo-600 hover:underline transition duration-150 truncate"
                                :title="conversation.title"
                            >
                                {{ conversation.title }}
                            </Link>
                            <div class="flex items-center space-x-4">
                                <div class="text-xs text-gray-700 text-right">
                                    {{ conversation.updatedAtDiff }}
                                </div>
                                <SecondaryButton
                                    v-if="conversations.length"
                                    @click="
                                        confirmConversationDeletion(
                                            conversation.id
                                        )
                                    "
                                    class="ms-3"
                                >
                                    <DeleteIcon class="h-5 w-5" />
                                </SecondaryButton>
                            </div>
                        </li>
                    </ul>
                    <p class="col-span-3" v-else>
                        Vous n'avez pas encore de conversation.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>

    <DialogModal :show="confirmingConversationDeletion" @close="closeModal">
        <template #title> Supprimer la conversation </template>

        <template #content>
            Êtes-vous sûr de vouloir supprimer cette conversation ? Cette action
            est irréversible.
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal"> Annuler </SecondaryButton>

            <DangerButton
                class="ms-3"
                :class="{ 'opacity-25': formDeleteConversation.processing }"
                :disabled="formDeleteConversation.processing"
                @click="deleteConversation"
            >
                Supprimer
            </DangerButton>
        </template>
    </DialogModal>
</template>
