<script setup>
import ActionMessage from "@/Components/ActionMessage.vue";
import FormSection from "@/Components/FormSection.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextareaInput from "@/Components/TextareaInput.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import LoadingIcon from "@/Components/LoadingIcon.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref, reactive, watch } from "vue";
import ConversationIcon from "@/Components/ConversationIcon.vue";
import { markdownIt } from "@/markdownit";

const props = defineProps({
    conversation: Object,
    models: Object,
    selectedModel: String,
    systemPromptTokenCount: Number,
});

const isAnswering = ref(false);
const chatBox = ref(null);
const selectedModel = ref(props.selectedModel);
const tokenCount = ref(
    props.conversation.token_count + props.systemPromptTokenCount
);
const title = ref(props.conversation.title);
let messages = reactive(props.conversation.messages);
let assistantMessage = {
    role: "assistant",
    body: "",
};

const isTokenLimitReached = computed(() => {
    const tokenLimit = props.selectedModel
        ? props.models[props.selectedModel].maxTokens
        : 0;
    return tokenCount >= tokenLimit;
});

const form = useForm({
    _method: "POST",
    prompt: "",
});

const send = async () => {
    if (isTokenLimitReached.value || isAnswering.value || form.processing) {
        return;
    }

    const prompt = form.prompt;

    form.post(
        route("messages.send", { conversationId: props.conversation.id }),
        {
            errorBag: "prompt",
            preserveScroll: true,
            onStart: () => {
                form.reset();
            },
            onSuccess: async () => {
                messages.push({
                    role: "user",
                    body: prompt,
                });

                messages.push(assistantMessage);
                isAnswering.value = true;

                await wait(2);
                scrollChatBox();

                answer();
            },
            onError: () => {
                isAnswering.value = false;
            },
        }
    );
};

const answer = async () => {
    if (isTokenLimitReached.value) {
        return;
    }

    // transform the form call below to a axios call
    axios
        .post(
            route("conversations.answer", {
                conversationId: props.conversation.id,
            })
        )
        .then((response) => {
            isAnswering.value = false;
        })
        .catch((error) => {
            isAnswering.value = false;
        });
};

const scrollChatBox = () => {
    if (chatBox.value) {
        chatBox.value.scrollTop = chatBox.value.scrollHeight;
    }
};

Echo.private(`conversations.${props.conversation.id}`)
    .listen("NewMessage", (e) => {
        tokenCount.value += e.tokenCount;
    })
    .listen("StreamText", (e) => {
        scrollChatBox();
        assistantMessage.body += e.text;
    })
    .listen("StopMessage", async (e) => {
        assistantMessage = {
            role: "assistant",
            body: "",
        };
        tokenCount.value += e.tokenCount;
        await wait(2);
        scrollChatBox();
        updateTitle();
    });

onMounted(() => {
    scrollChatBox();
});

const isTitleUpdating = ref(false);

const updateTitle = () => {
    // only called after assistant's messagez
    if (
        // Update title if there are 2 to 3 messages
        (props.conversation.messages.length > 1 &&
            props.conversation.messages.length < 4) ||
        // or if the number of messages is a multiple of 4 or 4+1
        ((props.conversation.messages.length % 4 === 1 ||
            props.conversation.messages.length % 4 === 0) &&
            // and number of messages is less than 31
            props.conversation.messages.length < 31)
    ) {
        isTitleUpdating.value = true;

        // transform the form call below to a axios call
        axios
            .post(
                route("conversations.updateTitle", {
                    conversationId: props.conversation.id,
                })
            )
            .then((response) => {
                title.value = response.data.title;
                isTitleUpdating.value = false;
            })
            .catch((error) => {
                console.log(error);
                isTitleUpdating.value = false;
            });
    }
};

const formUpdateSelectedModel = useForm({
    _method: "POST",
    model: props.selectedModel,
});

const updateSelectedModel = (value) => {
    formUpdateSelectedModel.model = value;
    selectedModel.value = value;
    formUpdateSelectedModel.post(
        route("models.select", { conversationId: props.conversation.id }),
        {
            preserveScroll: true,
            onSuccess: () => {},
        }
    );
};

const wait = async (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const promptExamples = [
    {
        prompt: "Bonjour. Qui es-tu ?",
    },
    {
        prompt: "Quel modèle de langage utilises-tu ?",
    },
    {
        prompt: "Raconte-moi une blague.",
    },
    {
        prompt: "lance une partie de pierre, papier, ciseaux.",
    },
];
</script>

<template>
    <AppLayout :title="`${conversation.title} - Conversations`">
        <template #header>
            <div class="flex justify-between space-x-6 items-center">
                <div>
                    <h2
                        class="flex space-x-2 items-baseline font-semibold text-xl text-gray-800 leading-tight"
                    >
                        <div>
                            {{ title }}
                        </div>
                        <div>
                            <LoadingIcon
                                v-if="isTitleUpdating"
                                class="-mb-1 w-5 h-5 animate-spin text-blue-900"
                            />
                        </div>
                    </h2>
                </div>
                <div
                    class="flex flex-col items-end md:items-center md:flex-row space-y-2 md:space-y-0 md:space-x-6"
                >
                    <Link
                        :href="route('conversations.index')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        title="Retour à la liste des conversations"
                    >
                        Retour
                    </Link>
                    <Link
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :href="route('conversations.create')"
                        title="Nouvelle conversation"
                    >
                        <ConversationIcon class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <FormSection @submitted="send">
                <template #title> Conversation </template>

                <template #description> Discutez avec l'assistant. </template>

                <template #form>
                    <div class="col-span-12">
                        <div>
                            <h3 class="text-lg font-semibold">Messages</h3>
                        </div>

                        <div class="">
                            <div v-if="!messages || !messages.length">
                                <ul
                                    class="bg-gray-100 p-4 mt-4 rounded-xl mb-6"
                                >
                                    <li>
                                        <p>Pas encore de message.</p>
                                    </li>
                                </ul>

                                <div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <button
                                            v-for="example in promptExamples"
                                            class="bg-indigo-100 p-4 rounded-md text-indigo-700 hover:text-indigo-900 border-2 border-indigo-700/20 hover:bg-indigo-200 hover:border-indigo-700/40 transition-all text-left"
                                            @click.prevent="
                                                form.prompt = example.prompt
                                            "
                                        >
                                            <p class="text-xs">
                                                {{ example.prompt }}
                                            </p>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <ul
                                v-else
                                ref="chatBox"
                                class="bg-gray-100 p-4 mt-4 rounded-xl flex flex-col space-y-8 max-h-[50vh] overflow-y-auto"
                            >
                                <li
                                    class="max-w-[80%] p-4 rounded-lg"
                                    :class="{
                                        'self-end bg-green-100 text-right':
                                            message.role === 'user',
                                        'bg-indigo-100 ':
                                            message.role === 'assistant',
                                    }"
                                    v-for="message in messages"
                                    :key="message.id"
                                    v-show="message.body.length > 0"
                                >
                                    <p
                                        class="prose"
                                        :class="{
                                            'prose-headings:text-green-950 text-green-900':
                                                message.role === 'user',
                                            'prose-headings:text-indigo-950 text-indigo-900':
                                                message.role === 'assistant',
                                        }"
                                        v-html="
                                            markdownIt.render(
                                                message.body ?? ''
                                            )
                                        "
                                    ></p>
                                    <span
                                        class="text-xs"
                                        :class="{
                                            'text-green-700':
                                                message.role === 'user',
                                            'text-indigo-700':
                                                message.role === 'assistant',
                                        }"
                                        >{{
                                            message.role === "assistant"
                                                ? "Assistant"
                                                : "Moi"
                                        }}</span
                                    >
                                </li>
                                <li
                                    v-show="
                                        isAnswering &&
                                        assistantMessage &&
                                        assistantMessage.body.length === 0
                                    "
                                    class="max-w-[80%] p-4 rounded-lg bg-indigo-100"
                                >
                                    <p class="text-indigo-900">Assistant</p>
                                    <LoadingIcon
                                        class="w-5 h-5 animate-spin text-indigo-900"
                                    />
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-span-12">
                        <InputLabel for="prompt" value="Votre message" />
                        <TextareaInput
                            id="prompt"
                            v-model="form.prompt"
                            type="text"
                            class="mt-1 block w-full"
                            @keydown.enter.ctrl="send"
                        />
                        <div class="flex justify-between items-center mt-2">
                            <div class="flex flex-col">
                                <InputError :message="form.errors.prompt" />
                                <InputError
                                    v-if="isTokenLimitReached"
                                    message="La limite de token est atteinte."
                                />
                            </div>
                            <div
                                class="text-xs text-gray-800"
                                :class="{
                                    'text-red-600': isTokenLimitReached,
                                }"
                            >
                                {{ tokenCount }} tokens
                            </div>
                        </div>
                    </div>
                </template>

                <template #actions>
                    <div class="flex space-x-4 items-center grow">
                        <InputLabel for="model" value="Modèle" />
                        <select
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            id="model"
                            @change="updateSelectedModel($event.target.value)"
                        >
                            <option
                                v-for="model in models"
                                :key="model.value"
                                :value="model.value"
                                :selected="model.value === props.selectedModel"
                            >
                                {{ model.name }}
                            </option>
                        </select>
                    </div>
                    <ActionMessage :on="form.recentlySuccessful" class="me-3">
                        Message envoyé.
                    </ActionMessage>

                    <PrimaryButton
                        :class="{
                            'opacity-25':
                                form.processing ||
                                isAnswering ||
                                isTokenLimitReached,
                        }"
                        :disabled="
                            form.processing ||
                            isAnswering ||
                            isTokenLimitReached
                        "
                    >
                        Envoyer
                    </PrimaryButton>
                </template>
            </FormSection>
        </div>
    </AppLayout>
</template>
