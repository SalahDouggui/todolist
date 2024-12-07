// Faire disparaître les messages de succès après 3 secondes
setTimeout(() => {
  const alert = document.querySelector(".alert");
  if (alert) {
    alert.classList.add("hide");
  }
}, 3000);

// Confirmation avant suppression d'une tâche
function confirmDeletion(link) {
  if (confirm("Êtes-vous sûr de vouloir supprimer cette tâche ?")) {
    // Ajouter l'animation avant de rediriger
    const taskItem = link.closest("li");
    taskItem.classList.add("hide");
    setTimeout(() => {
      window.location.href = link.href;
    }, 500); // Attendre que l'animation se termine
    return false;
  }
  return false; // Annuler la suppression si l'utilisateur clique sur "Annuler"
}
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

      // Vérifier si la tâche correspond aux filtres
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

  // Appliquer le filtre à chaque changement
  prioriteFilter.addEventListener("change", filterTasks);
  categorieFilter.addEventListener("change", filterTasks);
});
