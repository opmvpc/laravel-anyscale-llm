<script setup>
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { onMounted, ref, watch } from "vue";

const props = defineProps({
    thread: Object,
});

const isAnswering = ref(false);
const chatBox = ref(null);

const form = useForm({
    _method: "POST",
    prompt: "",
});

const formAnswer = useForm({
    _method: "POST",
});

const send = async () => {
    form.post(route("messages.send", { threadId: props.thread.id }), {
        errorBag: "prompt",
        preserveScroll: true,
        onSuccess: async () => {
            form.reset();
            scrollChatBox();
            await answer();
        },
    });
};

const answer = async () => {
    isAnswering.value = true;
    await wait(100);
    scrollChatBox();
    formAnswer.post(route("threads.answer", { threadId: props.thread.id }), {
        onBefore: () => {},
        preserveScroll: true,
        onSuccess: () => {
            isAnswering.value = false;
            scrollChatBox();
        },
        onError: () => {
            isAnswering.value = false;
        },
    });
};

const scrollChatBox = () => {
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight;
    }
};

onMounted(() => {
    scrollChatBox();
});

const wait = async (ms) => new Promise((resolve) => setTimeout(resolve, ms));
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ thread.title }}
            </h2>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="send">
                <template #title> Conversation </template>

                <template #description> Discutez avec l'assistant. </template>

                <template #form>
                    <div class="col-span-12">
                        <ul
                            ref="chatBox"
                            class="flex flex-col space-y-8 max-h-[50vh] overflow-y-auto"
                        >
                            <li
                                class="w-[80%] p-4 rounded-lg"
                                :class="{
                                    'self-end bg-green-100 text-right':
                                        message.role === 'user',
                                    'col-span-8 bg-blue-200':
                                        message.role === 'assistant',
                                }"
                                v-for="message in thread.messages"
                                :key="message.id"
                            >
                                <p>{{ message.body }}</p>
                                <span class="text-xs text-gray-800">{{
                                    message.role === "assistant"
                                        ? "Assistant"
                                        : "Moi"
                                }}</span>
                            </li>
                            <li
                                v-show="isAnswering"
                                class="w-[80%] p-4 rounded-lg col-span-8 bg-blue-200"
                            >
                                <svg
                                    class="w-5 h-5 animate-spin text-blue-900"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path
                                        d="M12 2C12.5523 2 13 2.44772 13 3V6C13 6.55228 12.5523 7 12 7C11.4477 7 11 6.55228 11 6V3C11 2.44772 11.4477 2 12 2ZM12 17C12.5523 17 13 17.4477 13 18V21C13 21.5523 12.5523 22 12 22C11.4477 22 11 21.5523 11 21V18C11 17.4477 11.4477 17 12 17ZM22 12C22 12.5523 21.5523 13 21 13H18C17.4477 13 17 12.5523 17 12C17 11.4477 17.4477 11 18 11H21C21.5523 11 22 11.4477 22 12ZM7 12C7 12.5523 6.55228 13 6 13H3C2.44772 13 2 12.5523 2 12C2 11.4477 2.44772 11 3 11H6C6.55228 11 7 11.4477 7 12ZM19.0711 19.0711C18.6805 19.4616 18.0474 19.4616 17.6569 19.0711L15.5355 16.9497C15.145 16.5592 15.145 15.9261 15.5355 15.5355C15.9261 15.145 16.5592 15.145 16.9497 15.5355L19.0711 17.6569C19.4616 18.0474 19.4616 18.6805 19.0711 19.0711ZM8.46447 8.46447C8.07394 8.85499 7.44078 8.85499 7.05025 8.46447L4.92893 6.34315C4.53841 5.95262 4.53841 5.31946 4.92893 4.92893C5.31946 4.53841 5.95262 4.53841 6.34315 4.92893L8.46447 7.05025C8.85499 7.44078 8.85499 8.07394 8.46447 8.46447ZM4.92893 19.0711C4.53841 18.6805 4.53841 18.0474 4.92893 17.6569L7.05025 15.5355C7.44078 15.145 8.07394 15.145 8.46447 15.5355C8.85499 15.9261 8.85499 16.5592 8.46447 16.9497L6.34315 19.0711C5.95262 19.4616 5.31946 19.4616 4.92893 19.0711ZM15.5355 8.46447C15.145 8.07394 15.145 7.44078 15.5355 7.05025L17.6569 4.92893C18.0474 4.53841 18.6805 4.53841 19.0711 4.92893C19.4616 5.31946 19.4616 5.95262 19.0711 6.34315L16.9497 8.46447C16.5592 8.85499 15.9261 8.85499 15.5355 8.46447Z"
                                    ></path>
                                </svg>
                                <span class="text-xs text-gray-800"
                                    >Assistant</span
                                >
                            </li>
                        </ul>
                    </div>
                    <div class="col-span-12">
                        <InputLabel for="prompt" value="Prompt" />
                        <TextInput
                            id="prompt"
                            v-model="form.prompt"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError
                            :message="form.errors.prompt"
                            class="mt-2"
                        />
                    </div>
                </template>

                <template #actions>
                    <ActionMessage :on="form.recentlySuccessful" class="me-3">
                        Message envoyé.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>
    </AppLayout>
</template>
