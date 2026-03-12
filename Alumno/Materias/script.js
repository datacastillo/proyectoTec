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
imagen = "../img/profesor1.png";

descripcion = `
El cálculo no es solo una rama de la matemática; es el lenguaje
del cambio y el movimiento. Mientras que el álgebra se ocupa de lo
estático, el cálculo nos permite modelar la realidad dinámica.
`;

}


if(materia === "poo"){

titulo = "POO";
profesor = "PROF. JUAN PEREZ";
info = "ING. SISTEMAS<br>JUANPEREZ@TEC.COM";
imagen = "../img/profesor2.png";

descripcion = `
La programación orientada a objetos permite organizar el software
en clases y objetos, facilitando la reutilización del código.
`;

}


if(materia === "bd"){

titulo = "BASE DE DATOS";
profesor = "PROF. MARIA LOPEZ";
info = "ING. BASES DE DATOS<br>MARIA@TEC.COM";
imagen = "../img/profesor3.png";

descripcion = `
Las bases de datos permiten almacenar y consultar grandes cantidades
de información dentro de sistemas informáticos.
`;

}


if(materia === "ilog"){

titulo = "ILOG";
profesor = "PROF. CARLOS MARTINEZ";
info = "ING. SISTEMAS COMPUTACIONALES<br>CMARTINEZ@TEC.COM";
imagen = "../img/profesor4.png";

descripcion = `
La ingeniería logística optimiza el flujo de productos,
información y recursos dentro de una empresa.
`;

}


if(materia === "gps"){

titulo = "GPS";
profesor = "PROF. ANA GARCIA";
info = "ING. SISTEMAS<br>AGARCIA@TEC.COM";
imagen = "../img/profesor5.png";

descripcion = `
El GPS permite determinar la ubicación geográfica en tiempo real
mediante satélites y sistemas de posicionamiento global.
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

}


function toggleMenu(){

let sidebar = document.querySelector(".sidebar");

sidebar.classList.toggle("active");

}