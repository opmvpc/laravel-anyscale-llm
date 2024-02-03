<script setup>
import FormSection from "@/Components/FormSection.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
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
            <FormSection @submitted="createThread">
                <template #title> Mes Threads </template>

                <template #description> Vos conversations. </template>

                <template #form>
                    <!-- Liste des liens vers les threads -->
                    <ul class="col-span-12" v-if="threads.length">
                        <li v-for="thread in threads" :key="thread.id">
                            <a :href="`/threads/${thread.id}`">{{
                                thread.title
                            }}</a>
                        </li>
                    </ul>

                    <!-- Message si aucun thread -->
                    <p class="col-span-12" v-else>
                        Vous n'avez pas encore de thread.
                    </p>
                </template>

                <template #actions>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Créer un thread
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>
    </AppLayout>
</template>
