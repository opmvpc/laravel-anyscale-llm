import MarkdownIt from "markdown-it";
import hljs from "highlight.js";

export const markdownIt = new MarkdownIt({
    highlight: function (str, lang) {
        // if lang is vue, use js
        if (lang === "vue") {
            lang = "ts";
        }
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value;
            } catch (__) {}
        }

        return ""; // use external default escaping
    },
});
