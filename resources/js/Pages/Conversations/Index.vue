<script setup>
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";

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
</script>

<template>
    <AppLayout title="Threads">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Threads
            </h2>
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
                        <li v-for="thread in threads" :key="thread.id">
                            <a
                                class="bg-white py-4 px-8 flex hover:bg-blue-50 transition duration-150"
                                :href="`/threads/${thread.id}`"
                                >{{ thread.title }}</a
                            >
                        </li>
                    </ul>
                    <p class="col-span-3" v-else>
                        Vous n'avez pas encore de thread.
                    </p>
                    <div class="flex col-span-12 justify-end pt-6">
                        <PrimaryButton
                            @click="createThread"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Créer un thread
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
