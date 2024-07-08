import {
  $,
  addClass,
  parents,
  removeClass,
} from 'uikit/src/js/util';
import UIkit from 'uikit/src/js/uikit';

export default {
  args: [
    'promptType',
    'textareaId',
    'spinnerId',
    'aiToolsId',
  ],

  props: {
    promptType: String,
    textareaId: String,
    spinnerId: String,
    aiToolsId: String,
    resultText: String,
  },

  data: {
    textContainer: null,
    spinner: null,
    modal: null,
    acceptButton: null,
    resultText: null,
  },

  computed: {
    textarea() {
      return $(this.textareaId);
    },
    spinner() {
      return $(this.spinnerId);
    },
    modal() {
      return $(`${this.textareaId}-modal`);
    },
    textContainer() {
      return $(`${this.textareaId}-text`);
    },
    acceptButton() {
      return $(`${this.textareaId}-accept-button`);
    },
  },

  events: [
    {
      name: 'click',

      el() {
        return this.$el;
      },

      handler(e) {
        e.preventDefault();

        // Hide dropdown
        const dropdown = parents(e.target, '.uk-dropdown');
        UIkit.dropdown(dropdown[0]).hide();

        // Send prompt
        this.sendPrompt();
      },
    },
    {
      name: 'click',

      el() {
        return this.acceptButton;
      },

      handler(e) {
        e.preventDefault();

        // Set value and hide modal
        this.textarea.value = this.resultText;
        UIkit.modal(this.modal).hide();
      },
    },
  ],

  methods: {
    sendPrompt() {
      removeClass(this.spinner, 'uk-hidden');

      // Collect data as formData
      const formData = new FormData();
      formData.append('name', this.aiToolsId);
      formData.append('text', this.textarea.value);
      formData.append('type', 'frontend');
      formData.append('promptType', this.promptType);

      // Send prompt
      fetch('/pimcore-ai-tools/prompts/text', {
        method: 'POST',
        body: formData,
      }).then((response) => response.json()).then((data) => {
        // Set result in modal and show it
        this.textContainer.innerHTML = data.result;
        UIkit.modal(this.modal).show();

        // Remove spinner
        addClass(this.spinner, 'uk-hidden');

        // Save result
        this.resultText = data.result;
      }).catch((error) => {
        console.error(error);
      });
    },
  },
};
