const TailwindHelper = {
  init() {
    this.setupDropdown();
    this.setupRefinementButton();
  },

  setupDropdown() {
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
    }
  },

  setupRefinementButton() {
    const refineButton = document.querySelector("#refine-button");

      refineButton?.addEventListener("click", (event) => {
        event.preventDefault();

        const alertModal = document.getElementById("alertModal");
        if (!alertModal.classList.contains("hidden")) {
          alertModal.classList.add("hidden");
          return;
        }

        const refinementTextarea = document.getElementById("refinement-textarea");
        const { promptType, aiToolsId } = refinementTextarea.dataset;
        const textContainer = document.querySelector("[id$='-text']");
        if (!textContainer) {
          console.error("Text container not found.");
          return;
        }

        const textareaId = textContainer.id.replace("-text", "");
        this.handleRefinement(textareaId, textContainer, promptType, aiToolsId, refinementTextarea)
          .catch(error => console.error("Error in handleRefinement:", error));
      });
  },

  handleDropdownAction(promptType, textareaId, spinnerId, aiToolsId) {
    this.sendPrompt(promptType, textareaId, spinnerId, aiToolsId)
      .catch(error => console.error("Error in sendPrompt:", error));
  },

  async sendPrompt(promptType, textareaId, spinnerId, aiToolsId) {
    try {
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

      const response = await fetch("/pimcore-ai-tools/prompts/text", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        console.error(`HTTP error! Status: ${response.status}`);
      }

      const data = await response.json();
      textContainer.innerHTML = data.result;
      modal.classList.remove("hidden");
      this.setupModalActions(modal, textareaId);
    } catch (error) {
      console.error("Error in sendPrompt:", error);
    } finally {
      const spinner = document.querySelector(spinnerId);
      spinner?.classList.add("hidden");
    }
  },

  setupModalActions(modal, textareaId) {
    const cancelButton = modal.querySelector(".cancel-button");
    const acceptButton = modal.querySelector(".accept-button");
    const textContainer = document.querySelector(`${textareaId}-text`);
    const textarea = document.querySelector(textareaId);

    cancelButton?.addEventListener("click", () => this.closeModal(modal));
    acceptButton?.addEventListener("click", () => {
      if (textarea && textContainer) {
        textarea.value = textContainer.innerHTML;
      }
      this.closeModal(modal);
    });
  },

  closeModal(modal) {
    modal?.classList.add("hidden");
  },

  async handleRefinement(textareaId, textContainer, promptType, aiToolsId, refinementTextarea) {
    const loadingOverlay = document.querySelector("#modal-loading-overlay");
    const mainText = textContainer.innerHTML;
    const refineText = refinementTextarea.value.trim();

    if (!refineText) {
      this.showWarningAlert();
      return;
    }

    loadingOverlay.classList.remove("hidden");
    const formData = new FormData();
    formData.append("name", aiToolsId);
    formData.append("text", `${mainText}\n${refineText}`);
    formData.append("type", "frontend-refinement");
    formData.append("promptType", promptType);

    try {
      const response = await fetch("/pimcore-ai-tools/prompts/text", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) {
        console.error(`HTTP error! Status: ${response.status}`);
      }

      const data = await response.json();
      textContainer.innerHTML = data.result;
      refinementTextarea.value = "";
    } catch (error) {
      console.error("Error refining text:", error);
    } finally {
      loadingOverlay.classList.add("hidden");
    }
  },

  showWarningAlert() {
    const modal = document.getElementById("alertModal");
    const alertClose = document.getElementById("alertClose");

    if (!modal.classList.contains("hidden")) {
      modal.classList.add("hidden");
      return;
    }

    modal.classList.remove("hidden");

    alertClose.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      modal.classList.add("hidden");
    });
  },
};

document.addEventListener("DOMContentLoaded", () => {
  TailwindHelper.init();
});

export default TailwindHelper;
