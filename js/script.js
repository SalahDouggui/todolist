// Faire disparaître les messages de succès après 3 secondes
setTimeout(() => {
  const alert = document.querySelector(".alert");
  if (alert) {
    alert.classList.add("hide");
  }
}, 3000);
// Filtrer les tâches dynamiquement
document.addEventListener("DOMContentLoaded", () => {
  const prioriteFilter = document.getElementById("filter-priorite");
  const categorieFilter = document.getElementById("filter-categorie");

  function filterTasks() {
    const prioriteValue = prioriteFilter.value.toLowerCase();
    const categorieValue = categorieFilter.value.toLowerCase();

    document.querySelectorAll(".tache").forEach((task) => {
      const taskPriorite = task.getAttribute("data-priorite").toLowerCase();
      const taskCategorie = task.getAttribute("data-categorie").toLowerCase();

      const matchesPriorite = !prioriteValue || taskPriorite === prioriteValue;
      const matchesCategorie =
        !categorieValue || taskCategorie === categorieValue;

      if (matchesPriorite && matchesCategorie) {
        task.classList.remove("hidden");
      } else {
        task.classList.add("hidden");
      }
    });
  }

  prioriteFilter.addEventListener("change", filterTasks);
  categorieFilter.addEventListener("change", filterTasks);
});
