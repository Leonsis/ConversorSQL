document.addEventListener('DOMContentLoaded', function() {

    // Efeito de mostrar e esconter testo
    const button = document.getElementById('toggleButton');
    const texto = document.getElementById('texto');

    button.addEventListener('click', function() {

        if(texto.style.display === 'none'){
            texto.style.display = 'block';
        }else{
            texto.style.display = 'none';
        }

    });
});
