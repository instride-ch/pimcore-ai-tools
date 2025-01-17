const TailwindHelper = {
  init() {
    // Dropdown-Handling
    const dropdownButton = document.querySelector("#dropdown-button");
    const dropdownMenu = document.querySelector("#dropdown-menu");

    if (dropdownButton && dropdownMenu) {
      dropdownButton.addEventListener("click", (event) => {
        event.stopPropagation();
        const isExpanded = dropdownButton.getAttribute("aria-expanded") === "true";
        dropdownButton.setAttribute("aria-expanded", !isExpanded);
        dropdownMenu.classList.toggle("hidden");
      });

      document.addEventListener("click", () => {
        dropdownButton.setAttribute("aria-expanded", "false");
        dropdownMenu.classList.add("hidden");
      });
    }

    // Delegated Event Listener for Dropdowns
    dropdownMenu.addEventListener("click", (event) => {
      const option = event.target.closest("[data-prompt-type]");
      if (option) {
        event.preventDefault();
        const promptType = option.getAttribute("data-prompt-type");
        const textareaId = option.getAttribute("data-textarea-id");
        const spinnerId = option.getAttribute("data-spinner-id");
        const aiToolsId = option.getAttribute("data-ai-tools-id");

        this.handleDropdownAction(promptType, textareaId, spinnerId, aiToolsId);
      }
    });
  },

  handleDropdownAction(promptType, textareaId, spinnerId, aiToolsId) {
    this.sendPrompt(promptType, textareaId, spinnerId, aiToolsId);
  },

  sendPrompt(promptType, textareaId, spinnerId, aiToolsId) {
    const spinner = document.querySelector(spinnerId);
    const textarea = document.querySelector(textareaId);
    const textContainer = document.querySelector(`${textareaId}-text`);
    const modal = document.querySelector(`${textareaId}-modal`);

    if (!spinner || !textarea || !modal || !textContainer) {
      console.error("Missing Elements for TailwindHelper.");
      return;
    }

    spinner.classList.remove("hidden");

    const formData = new FormData();
    formData.append("name", aiToolsId);
    formData.append("text", textarea.value);
    formData.append("type", "frontend");
    formData.append("promptType", promptType);

    fetch("/pimcore-ai-tools/prompts/text", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        textContainer.innerHTML = data.result;

        modal.classList.remove("hidden");

        this.setupModalActions(modal, textareaId);

        spinner.classList.add("hidden");
      })
      .catch((error) => {
        console.error(error);
        spinner.classList.add("hidden");
      });
  },

  setupModalActions(modal, textareaId) {
    const cancelButton = modal.querySelector(".cancel-button");
    const acceptButton = modal.querySelector(".accept-button");
    const textContainer = document.querySelector(`${textareaId}-text`);
    const textarea = document.querySelector(textareaId);

    if (cancelButton) {
      cancelButton.addEventListener("click", () => {
        this.closeModal(modal);
      });
    }

    if (acceptButton) {
      acceptButton.addEventListener("click", () => {
        if (textarea && textContainer) {
          textarea.value = textContainer.innerHTML;
        }
        this.closeModal(modal);
      });
    }
  },

  closeModal(modal) {
    if (modal) {
      modal.classList.add("hidden");
    }
  },
};

document.addEventListener("DOMContentLoaded", () => {
  TailwindHelper.init();
});

export default TailwindHelper;
