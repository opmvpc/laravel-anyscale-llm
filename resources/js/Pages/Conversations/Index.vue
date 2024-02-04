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

const props = defineProps({
    threads: Array,
});

const form = useForm({
    _method: "POST",
});

const createThread = () => {
    form.post(route("threads.create"), {
        preserveScroll: true,
    });
};

const formDeleteThread = useForm({
    _method: "DELETE",
});
const confirmingThreadDeletion = ref(false);
const ThreadIdToDelete = ref(null);

const confirmThreadDeletion = (id) => {
    ThreadIdToDelete.value = id;
    confirmingThreadDeletion.value = true;
};

const deleteThread = () => {
    formDeleteThread.delete(route("threads.delete", ThreadIdToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingThreadDeletion.value = false;
        },
        onError: () => {
            confirmingThreadDeletion.value = false;
        },
    });
};

const closeModal = () => {
    confirmingThreadDeletion.value = false;
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
                        @click="createThread"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Nouvelle conversation
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <SectionTitle>
                    <template #title> Mes Conversation </template>
                    <template #description>
                        <p>
                            Vous pouvez créer un nouveau fil de discussion pour
                            discuter avec l'assistant.
                        </p>
                    </template>
                </SectionTitle>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <!-- Liste des liens vers les threads -->
                    <ul
                        class="mt-5 md:mt-0 md:col-span-2 flex flex-col divide-y-2 rounded-xl overflow-hidden shadow"
                        v-if="threads.length"
                    >
                        <li
                            v-for="thread in threads"
                            :key="thread.id"
                            class="bg-white py-4 px-8 flex hover:bg-indigo-50 transition duration-150 items-center justify-between space-x-4"
                        >
                            <Link
                                :href="`/threads/${thread.id}`"
                                class="grow hover:text-indigo-600 hover:underline transition duration-150 truncate"
                                :title="thread.title"
                            >
                                {{ thread.title }}
                            </Link>
                            <div class="flex items-center space-x-4">
                                <div class="text-xs text-gray-700 text-right">
                                    {{ thread.updatedAtDiff }}
                                </div>
                                <SecondaryButton
                                    v-if="threads.length"
                                    @click="confirmThreadDeletion(thread.id)"
                                    class="ms-3"
                                >
                                    <DeleteIcon class="h-5 w-5" />
                                </SecondaryButton>
                            </div>
                        </li>
                    </ul>
                    <p class="col-span-3" v-else>
                        Vous n'avez pas encore de thread.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>

    <DialogModal :show="confirmingThreadDeletion" @close="closeModal">
        <template #title> Supprimer la conversation </template>

        <template #content>
            Êtes-vous sûr de vouloir supprimer cette conversation ? Cette action
            est irréversible.
        </template>

        <template #footer>
            <SecondaryButton @click="closeModal"> Annuler </SecondaryButton>

            <DangerButton
                class="ms-3"
                :class="{ 'opacity-25': formDeleteThread.processing }"
                :disabled="formDeleteThread.processing"
                @click="deleteThread"
            >
                Supprimer
            </DangerButton>
        </template>
    </DialogModal>
</template>
