function toggleMaterias(){

let lista = document.getElementById("materiasLista");

if(lista.style.display === "block"){
lista.style.display = "none";
}else{
lista.style.display = "block";
}

}


function mostrarMateria(materia){

let contenido = document.getElementById("contenido");

let titulo = "";
let profesor = "";
let info = "";
let descripcion = "";
let imagen = "";


if(materia === "calculo"){

titulo = "CALCULO";
profesor = "PROF. INOCENCIO JIRAEFALES";
info = "ING. VECINDAD DEL CHAVO<br>JIRAEFALES@VECINDAD.COM";
imagen = "Imagenes/profesor1.png";

descripcion = `
El cálculo no es solo una rama de la matemática; es el lenguaje
del cambio y el movimiento. Mientras que el álgebra se ocupa de lo
estático, el cálculo nos permite modelar la realidad dinámica:
desde la trayectoria de un cohete hasta la propagación de una epidemia.
`;

}


if(materia === "poo"){

titulo = "POO";
profesor = "PROF. JUAN PEREZ";
info = "ING. SISTEMAS<br>JUANPEREZ@TEC.COM";
imagen = "Imagenes/profesor2.png";

descripcion = `
La programación orientada a objetos permite organizar el software
en clases y objetos, facilitando la reutilización del código y
la creación de sistemas más estructurados y mantenibles.
`;

}


if(materia === "bd"){

titulo = "BASE DE DATOS";
profesor = "PROF. MARIA LOPEZ";
info = "ING. BASES DE DATOS<br>MARIA@TEC.COM";
imagen = "Imagenes/profesor3.png";

descripcion = `
Las bases de datos permiten almacenar, organizar y consultar
grandes cantidades de información de forma eficiente dentro
de sistemas informáticos modernos.
`;

}

if(materia === "ilog"){

titulo = "ILOG";
profesor = "PROF. CARLOS MARTINEZ";
info = "ING. SISTEMAS COMPUTACIONALES<br>CMARTINEZ@TEC.COM";
imagen = "../img/profesor4.png";

descripcion = `
La ingeniería logística (ILOG) se enfoca en la planeación,
organización y control eficiente del flujo de productos,
información y recursos dentro de una empresa. Permite
optimizar procesos de transporte, almacenamiento y
distribución para mejorar la productividad y reducir costos.
`;

}

if(materia === "ilog"){

titulo = "ILOG";
profesor = "PROF. CARLOS MARTINEZ";
info = "ING. SISTEMAS COMPUTACIONALES<br>CMARTINEZ@TEC.COM";
imagen = "../img/profesor4.png";

descripcion = `
La ingeniería logística (ILOG) se enfoca en la planeación,
organización y control eficiente del flujo de productos,
información y recursos dentro de una empresa. Permite
optimizar procesos de transporte, almacenamiento y
distribución para mejorar la productividad y reducir costos.
`;

}
if(materia === "gps"){

titulo = "GPS";
profesor = "PROF. ANA GARCIA";
info = "ING. SISTEMAS<br>AGARCIA@TEC.COM";
imagen = "../img/profesor5.png";

descripcion = `
La materia de GPS aborda el uso de tecnologías de
posicionamiento global para determinar la ubicación
geográfica de objetos o personas en tiempo real.
Se estudian aplicaciones en navegación, monitoreo
de transporte, mapas digitales y sistemas de
localización en diferentes plataformas tecnológicas.
`;

}


contenido.innerHTML = `

<div class="materia-container">

<h1 class="titulo-materia">${titulo}</h1>

<div class="profesor-info">

<img src="${imagen}" class="foto-profesor">

<div class="barra-profesor">

<h3>${profesor}</h3>

<p>${info}</p>

</div>

</div>

<div class="descripcion">
${descripcion}
</div>

</div>
`;


/* cerrar menu en celular cuando seleccionan materia */

let sidebar = document.querySelector(".sidebar");

if(window.innerWidth <= 768){
sidebar.classList.remove("active");
}

}


function toggleMenu(){

let sidebar = document.querySelector(".sidebar");

sidebar.classList.toggle("active");

}