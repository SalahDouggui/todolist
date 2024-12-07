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
