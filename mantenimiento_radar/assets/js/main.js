
// CLICK
document.addEventListener("click", function(e){

    if(e.target.matches(".btn-ronda")){
        e.target.innerText = "Cambiando...";
    }

    const btn = e.target.closest(".btn-eliminar");

    if(btn){
        e.preventDefault();

        if(confirm("⚠️ ¿Seguro que deseas eliminar este equipo?")){
            window.location.href = btn.href;
        }
    }

});


// ALERTAS AUTOMÁTICAS
setTimeout(() => {
    document.querySelectorAll(".alert").forEach(alert => {
        alert.style.transition = "opacity 0.5s";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    });
}, 3000);


// FORM SUBMIT
document.addEventListener("submit", function(e){
    const btn = e.target.querySelector("button[type='submit']");
    if(btn){
        btn.innerText = "Guardando...";
        btn.disabled = true;
    }
});