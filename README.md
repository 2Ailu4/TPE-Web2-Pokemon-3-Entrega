<p align="center">
  <img align="center" src="imgs/gifs/TituloPokemon.gif">
</p>

# TPE-Web2-Pokemon-3°Entrega
Juego-Pokemon multijugador

## :computer: Integrantes:
  * [Mariano Jesus Hiese][websiteM]
  * [Ailen Peralta Amado][websiteA]

## Ayudante:
  * [Bruno de la Penna]

# Descripcion
Siguiendo la idea de la 2°Entrega se agrega a nuestro juego Pokemon una API REST pública para brindar servicio y se puedan integrar y ser consumida por otros sistemas/clientes

 <p align="center">
  <img align="center" src="imgs/gifs/pokemons.gif">
</p>

## :Desplegar el sitio :
	* Descargar instalar XAMPP 
  	* En la carpeta "C:\xampp\htdocs\" mediante git hub crear un repositorio en git bash y clonar el repositorio "https://github.com/2Ailu4/	TPE-WEB2-Juego-Pokemon"
	* En XAMP habilitar apache y mysql
	* 

> Mapeo de Endpints validos:
## <ins> Pokemon: </ins> 
| **Verbo** |        **Endpoint**				| 
|-----------|-----------------------------------|
|     GET   |      api/pokemon      			|  
|     GET   |  api/pokemon/:id  				| 
|    PATCH  |  api/pokemon/:id  				| 
|     PUT   |       api/pokemon        	        | 

## <ins> Aprendizaje: </ins> 
| **Verbo** |        **Endpoint**				| 
|-----------|-----------------------------------|
|     GET   |      api/aprendizaje      		|  
|     GET   |  api/aprendizaje/:id_pok/:id_mov  | 
|    PATCH  |  api/aprendizaje/:id_pok/:id_mov  | 
|     PUT   |       api/aprendizaje             | 


## <ins> Movimiento: </ins> 
| **Verbo** |        **Endpoint**				| 
|-----------|-----------------------------------|
|     GET   |      api/movimiento      	     	|  
|     GET   |  api/movimiento/:id               | 
|    PATCH  |  api/movimiento/:id               | 
|     PUT   |       api/movimiento              | 



# Autenticacion
Para poder utilizar las funcionalidades de Actualizar e Insertar e las tablas de Aprendizaje, Pokemon y Movimiento se debe autenticar que el usuario sea valido. Para ello se deberan seguir los siguientes pasos:
## Iniciar Sesion
  * Abrir la aplicacion de **Postman**
  * Seleccionar el verbo **GET**
  * Escribir la ruta donde guardamos el trabajo, por ej: **http://localhost/web2/TPE-Web2-Hiese-Peralta-3%c2%b0Entrega**
  * A esa ruta le agregamos el endopoint **/api/usuario/token**
  * Nos dirigirimos a la opcion de **'Authorization'**
  * En 'Tipo de Autenticación'(Auth Type) seleccionamos **'Basic Auth'**
  * Escribimos nuestro usuario, por ej: **Username:** webadmin y **Password:** admin
  * Esto nos devolvera un **token** como response, copiarlo y guardarlo en un block de notas
  * Cambiamos el Auth Type a **'Bearer Token'**
  * Pegamos el token que guardamos en el block de notas en la casilla vacia
  * En la ruta cambiamos el **api/usuario/token** por la consulta que se desea realizar, por ej actualizar una relacion de Aprendizaje, entonces la ruta nos quedaria **api/aprendizaje/:id_pok/:id_mov**
  * Cambiamos (de ser necesario) el verbo, siguiendo el ejemplo anterior seleccionaremos la opcion **PATCH**
  * Por ultimo enviamos presionando sobre **SEND**

[NOTA]: Tener en cuenta que la sesion caducara en 1 hora!!! Pasado este tiempo debera volver a iniciar sesion para seguir realizando peticiones.



# ***Endpoints***
> Tabla Aprendizaje:
 - <ins>GET All:</ins> **api/aprendizaje**
	> DEFAULT
		- Si no se declara ninguna restriccion se listaran todos los pokemons cada uno con su informacion y un arreglo delos movimientos que aprendio o aprendera mas adelante y en nivel en que esto sucede.
		Formato:
		{
			{
				"id": 1,
				"nro_pokedex": 1,
				"nombre": "Bulbasaur",
				"tipo": "Planta",
				"fecha_captura": "2018-11-12 19:16:51",
				"peso": 70,
				"FK_id_entrenador": 1,
				"imagen_pokemon": "images/pokemons/Bulbasaur.jpg",
				"movimientos": [
								{
									"id_movimiento": 2,
									"nombre_movimiento": "Llamarada",
									"tipo_movimiento": "Fuego",
									"poder_movimiento": 110,
									"precision_movimiento": 85,
									"descripcion_movimiento": "Un poderoso ataque de fuego con posibilidad de quemar al oponente.",
									"nivel_aprendizaje": 10
								},
								...
							]
			},
			{
				"id": 2,
				"nro_pokedex": 4,
				"nombre": "Charmander",
				"tipo": "Fuego",
				"fecha_captura": "2022-01-07 16:16:00",
				"peso": 85,
				"FK_id_entrenador": 2,
				"imagen_pokemon": "images/pokemons/Charmander.jpg",
				"movimientos": [
					{
						"id_movimiento": 3,
						"nombre_movimiento": "Rayo",
						"tipo_movimiento": "Electrico",
						"poder_movimiento": 90,
						"precision_movimiento": 100,
						"descripcion_movimiento": "Un ataque de rayo que puede paralizar al objetivo.",
						"nivel_aprendizaje": 20
					},
					...
				]
			},
			...
		}
    > Si se consulta por id_movimiento (siempre y cuando no se filtre tambien por id_pokemon) se obtendra la siguiente estructura :
	- [Estructura de Movimientos]:	api/aprendizaje?id_movimiento=2
		- **Response:**		
				{
					"id_movimiento": 2,
					"nombre_movimiento": "Llamarada",
					"tipo_movimiento": "Fuego",
					"poder_movimiento": 110,
					"precision_movimiento": 85,
					"descripcion_movimiento": "Un poderoso ataque de fuego con posibilidad de quemar al oponente",
					"nivel_aprendizaje": 10
					"pokemons" : [
						Pokemon1,
						Pokemon2,
						...,
						Pokemon11,
						...,
						Pokemon25
					]
				}

	> Ordenamiento
		- Si se especifica la query reservada "sort_" antes del nombre del campo por el que se desea odenar se obtendran los elementos de la relacion Aprendizaje ordenados por ese criterio, tener en cuenta que los movimientos estan contenidos en el pokemon (arreglo de movimientos).  

	# <ins> Sorts: Pokemon </ins> 
	| **Field**     |        **Type**	     | **Example** | 
	|---------------|------------------------|-------------|
	|   nro_pokedex |        Int(1) > 0  	 | ?sort_nro_pokedex=ASC/DESC|
	|     nombre    |        Int(30) > 0   	 | ?sort_nombre=ASC/DESC|
	|      tipo     |        Varchar(20)      | ?sort_tipo=ASC/DESC |
	| fecha_captura |        Date         	 | ?sort_fecha_captura=ASC/DESC|
	|     peso      |        Int(11) > 0       | ?sort_peso=ASC/DESC|
	| id_entrenador |        Int(12) > 0       | ?sort_entrenador=ASC/DESC|

	# <ins> Sorts: Aprendizaje</ins> 
	| **Field**       |        **Type**	     | **Example** |
	|-----------------|----------------------|-------------|
	|   id_pokemon    |        Int(11) > 0     | ?sort_id_pokemon=ASC/DESC|
	|  id_movimiento  |        Int(11) > 0  	 | ?sort_id_movimiento=ASC/DESC|
	|nivel_aprendizaje|        Int(11) > 0   	 | ?sort_nivel_aprendizaje=ASC/DESC|

	# <ins> Sorts: Movimiento </ins> 
	| **Field**     		|        **Type**	     | **Example** |
	|-----------------------|------------------------|-------------|
	|nombre_movimiento 		|        Varchar(50)		  	 | ?sort_nombre_movimiento=ASC/DESC|
	|tipo_movimiento    	|        Varchar(20)		   	 | ?sort_tipo_movimiento=ASC/DESC|
	|poder_movimiento     	|        Int(11) > 0         	 | ?sort_poder_movimiento=ASC/DESC|
	|presicion_movimiento 	|        Int(11) > 0       | ?sort_presicion_movimiento=ASC/DESC|
	|descripcion_movimiento |        Text		     | ?sort_descripcion_movimiento=ASC/DESC|
	

		- Por ejemplo: si se desea ordenar por nombre de movimiento, el endpoint nos quedara: api/aprendizaje?sort_nombre_movimiento. Y el resultado de esta consulta nos devolvera los pokemons ordenados teniendo en cuenta el que cuente con el nombre del movimiento "mas chico" se colocara primero e internamente a cada pokemon ordenara los movimientos por el criterio previamnete especificado. 
		- Si solo se especifica sort_<Nombre del campo> los elementos se listaran por defecto de forma ascendente, si se desea ordenar de forma descendente bastara con agregar a la consulta "=DESC", por ende el endpoint nos quedara:api/aprendizaje?sort_nombre_movimiento=DESC


<!-- MARIANN -->


> Tabla Movimiento: 
 - [Respuesta]: Devuelve los resultados en Formato JSON:
 - [Estructura de Movimiento]:
	- **Response:**:
				{
					"id_movimiento": 5,
					"nombre_movimiento": "Hidrobomba",
					"tipo_movimiento": "Agua",
					"poder_movimiento": 110,
					"precision_movimiento": 80,
					"descripcion_movimiento": "Un fuerte ataque de agua con alta potencia pero baja precisión."
				}

 - <ins>GET All:</ins> **api/movimiento**
	- [Estructura de Movimientos]:
		- **Response:**
				[
					Movimiento1,
					Movimiento2,
					...,
					Movimiento6,
					Movimiento7
					...,
					Movimiento12
				]

 - <ins>GET por id:</ins> **api/movimiento/:id**
	Devuelve la informacion del movimiento con id = :id

 - <ins>PATCH:</ins> **api/movimiento/:id** 

 - <ins>PUT:</ins> **api/movimiento**


> Tabla de Pokemon
 - [Estructura de Pokemon]:
	- **Response:**:
			{
				"id": 15,
				"nro_pokedex": 26,
				"nombre": "Raichu",
				"tipo": "Electrico",
				"fecha_captura": "2024-11-16 21:45:18",
				"peso": 300,
				"FK_id_entrenador": 1,
				"imagen_pokemon": "images/pokemons/Raichu.jpg"
			}

 - <ins>GET All:</ins> **api/pokemon**
	 - [Estructura de Pokemon]:
		- **Response:**
			[
				Pokemon1,
				Pokemon2,
				...,
				Pokemon11,
				...,
				Pokemon25
			]
			
 - <ins>GET por id:</ins> **api/pokemon/:id**

 - <ins>PATCH:</ins> **api/pokemon/:id**

 - <ins>PUT:</ins> **api/pokemon**


[Ejemplos de algunas combinaciones posibles:]
	api/aprendizaje?nombre_movimiento=Impactrueno&sort_peso=DESC&nombre=PikaChU
	api/aprendizaje?sort_nombre_movimiento&limit=2&nombre=PikaChU&page=2

	api/aprendizaje?id_pokemon=2&nombre=charmander
	api/aprendizaje?sort_nombre=Desc&id_movimiento=1



# :rocket: Tecnologias:

<div>
	<code><img width="50" src="https://user-images.githubusercontent.com/25181517/192108374-8da61ba1-99ec-41d7-80b8-fb2f7c0a4948.png" alt="GitHub" title="GitHub"/></code>
	<code><img width="50" src="https://user-images.githubusercontent.com/25181517/192158954-f88b5814-d510-4564-b285-dff7d6400dad.png" alt="HTML" title="HTML"/></code>
	<code><img width="50" src="https://user-images.githubusercontent.com/25181517/183898674-75a4a1b1-f960-4ea9-abcb-637170a00a75.png" alt="CSS" title="CSS"/></code>
	<code><img height="55"width="50" src="https://user-images.githubusercontent.com/25181517/117447155-6a868a00-af3d-11eb-9cfe-245df15c9f3f.png" alt="JavaScript" title="JavaScript"/></code>
	<code><img width="50" src="https://user-images.githubusercontent.com/25181517/183570228-6a040b9f-3ddf-47a2-a201-743121dac664.png" alt="php" title="php"/></code>
	<code><img width="50" src="https://user-images.githubusercontent.com/25181517/183896128-ec99105a-ec1a-4d85-b08b-1aa1620b2046.png" alt="MySQL" title="MySQL"/></code>
</div>


[websiteM]: https://github.com/MarianoHR07

[websiteA]: https://github.com/2Ailu4
