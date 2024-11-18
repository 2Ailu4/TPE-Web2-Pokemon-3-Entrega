<p align="center">
  <img align="center" src="imgs/gifs/TituloPokemon.gif">
</p>

# TPE-Web2-Pokemon-3°Entrega
**Juego Pokémon multijugador**

## 📋 **Integrantes**
  * [Mariano Jesus Hiese][websiteM]
  * [Ailen Peralta Amado][websiteA]

## Ayudante:
  * Bruno de la Penna

---

## 📖 **Descripción**
Siguiendo la idea de la 2°Entrega se agrega a nuestro juego Pokemon una API REST pública para brindar servicio y se puedan integrar y ser consumida por otros sistemas/clientes

 <p align="center">
  <img align="center" src="imgs/gifs/pokemons.gif">
</p>

---

## 🌐 **Desplegar el sitio**
1. Descargar e instalar **XAMPP**.
2. En la carpeta `C:\xampp\htdocs\`, clonar este repositorio desde GitHub:
   ```bash
   git clone https://github.com/2Ailu4/TPE-WEB2-Juego-Pokemon
   ```
3. Habilitar **Apache** y **MySQL** en XAMPP.
4. Crear un esquema en la base de datos.
5. Configurar el archivo config.php y asignarle a la constante MYSQL_DB = <nombre del esquema de la DB>

---

## 🛠️ **Mapeo de Endpoints**

### **Pokémon**
| **Verbo HTTP** | **Endpoint**             |
|-----------------|--------------------------|
| `GET`          | `/api/pokemon`           |
| `GET`          | `/api/pokemon/:id`       |
| `PATCH`        | `/api/pokemon/:id`       |
| `PUT`          | `/api/pokemon`           |

### **Aprendizaje**
| **Verbo HTTP** | **Endpoint**                     |
|-----------------|----------------------------------|
| `GET`          | `/api/aprendizaje`              |
| `GET`          | `/api/aprendizaje/:id_pok/:id_mov` |
| `PATCH`        | `/api/aprendizaje/:id_pok/:id_mov` |
| `PUT`          | `/api/aprendizaje`              |

### **Movimiento**
| **Verbo HTTP** | **Endpoint**             |
|-----------------|--------------------------|
| `GET`          | `/api/movimiento`        |
| `GET`          | `/api/movimiento/:id`    |
| `PATCH`        | `/api/movimiento/:id`    |
| `PUT`          | `/api/movimiento`        |

---

## 🔒 **Autenticación**
Para realizar operaciones de actualización (`PATCH`) e inserción (`PUT`) en las tablas `Aprendizaje`, `Pokémon` y `Movimiento`, se debe autenticar el usuario. Para ello se deberan seguir los siguientes pasos:
## Iniciar Sesion
  * Abrir la aplicacion **Postman**
  * Seleccionar el verbo **GET**
  * Escribir la ruta donde guardamos el trabajo, por ej: **http://localhost/web2/miJuegoPokemon**
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

> **Nota:** Tener en cuenta que la sesion caducara en 1 hora!!! Pasado este tiempo debera volver a iniciar sesion para seguir realizando peticiones.

---

## 📂 **Ejemplo de Endpoints**
**[Respuestas]: Devuelve los resultados en Formato JSON:**

## **Tabla Aprendizaje**
## GET ALL: Obtener todos los registros:
**`GET /api/aprendizaje`** <br/>
DEFAULT:
- Si no se declara ninguna restriccion se listaran todos los pokemons cada uno con su informacion y un arreglo delos movimientos que aprendio o aprendera mas adelante y en nivel en que esto sucede.
**Formato:** <br/>
```json
[
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
			}
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
			}
		]
	}
  }
]
```
- Si se consulta por id_movimiento (siempre y cuando no se filtre tambien por id_pokemon) se obtendra la siguiente estructura :
[Estructura de Movimientos]:	<br/>
Ejemplo: **api/aprendizaje?id_movimiento=2** <br/>
**Response:** <br/>
```json 		
	{
		"id_movimiento": 2,
		"nombre_movimiento": "Llamarada",
		"tipo_movimiento": "Fuego",
		"poder_movimiento": 110,
		"precision_movimiento": 85,
		"descripcion_movimiento": "Un poderoso ataque de fuego con posibilidad de quemar al oponente",
		"nivel_aprendizaje": 10
		"pokemons : "[
			"Pokemon1",
			"Pokemon2",
			"...",
			"Pokemon11",
			"...",
			"Pokemon25"
		]
	}
```
**`Ordenamiento`**
 - Si se especifica la query reservada "sort_" antes del nombre del campo por el que se desea odenar se obtendran los elementos de la relacion Aprendizaje ordenados por ese criterio, tener en cuenta que los movimientos estan contenidos en el pokemon (arreglo de movimientos).  

# <ins> Sorts: Pokemon </ins> 
| **Field**     |        **Type**	     | **Example** | 
|---------------|------------------------|-------------|
|   `nro_pokedex` |        `Int(11) > 0`  	 | `?sort_nro_pokedex=ASC/DESC`|
|     `nombre`    |        `Int(30) > 0`   	 | `?sort_nombre=ASC/DESC`|
|      `tipo`     |        `Varchar(20)`      | `?sort_tipo=ASC/DESC` |
| `fecha_captura` |        `Date`         	 | `?sort_fecha_captura=ASC/DESC`|
|     `peso`      |        `Int(11) > 0`       | `?sort_peso=ASC/DESC`|
| `id_entrenador` |        `Int(12) > 0`       | `?sort_entrenador=ASC/DESC`|

# <ins> Sorts: Aprendizaje</ins> 
| **Field**       |        **Type**	     | **Example** |
|-----------------|----------------------|-------------|
|   `id_pokemon`    |        `Int(11) > 0`     | `?sort_id_pokemon=ASC/DESC`|
|  `id_movimiento`  |        `Int(11) > 0`  	 | `?sort_id_movimiento=ASC/DESC`|
|`nivel_aprendizaje`|        `Int(11) > 0`   	 | `?sort_nivel_aprendizaje=ASC/DESC`|

# <ins> Sorts: Movimiento </ins> 
| **Field**     		|        **Type**	     | **Example** |
|-----------------------|------------------------|-------------|
|`nombre_movimiento` 		|        `Varchar(50)`		  	 | `?sort_nombre_movimiento=ASC/DESC`|
|`tipo_movimiento`    	|        `Varchar(20)`		   	 | `?sort_tipo_movimiento=ASC/DESC`|
|`poder_movimiento`     	|        `Int(11) > 0`         	 | `?sort_poder_movimiento=ASC/DESC`|
|`presicion_movimiento` 	|        `Int(11) > 0`       | `?sort_presicion_movimiento=ASC/DESC`|
|`descripcion_movimiento` |        `Text`		     | `?sort_descripcion_movimiento=ASC/DESC`|

    Por ejemplo: si se desea ordenar por nombre de movimiento, el endpoint nos quedara: api/aprendizaje?sort_nombre_movimiento. Y el resultado de esta consulta nos devolvera los pokemons ordenados teniendo en cuenta el que cuente con el nombre del movimiento "mas chico" se colocara primero e internamente a cada pokemon ordenara los movimientos por el criterio previamnete especificado. 
    Si solo se especifica sort_<Nombre del campo> los elementos se listaran por defecto de forma ascendente, si se desea ordenar de forma descendente bastara con agregar a la consulta "=DESC", por ende el endpoint nos quedara:api/aprendizaje?sort_nombre_movimiento=DESC


#### `Filtrado y ordenamiento`
- Si se especifica en la query solo el nombre del campo y el valor que se desea buscar se filtrara por el mismo.
    Por ejemplo: si se desea listar todas las relaciones de Aprendizaje que contenga al pokemon con nombre Bulbasaur, el endpoint nos quedaria: api/aprendizaje?nombre=Bulbasaur  

### [Recomendaciones]: 
- Si bien es posible realizar busquedas por todos los campos, a la hora de filtrar, considerar combinaciones de filtros de tipo **Secundario**, estos se clasifican asi, por ser claves parciales o unicas. 
- Luego existen campos **Opcionales** que combinados con los anteriores, posibilitan la obtencion de un conjunto mas acotado.
- En el caso de Aprendizaje, al tener dos campos de tipo **KEY** de filtrarse por uno u otro, el filtro se convierte en **Secundario**. 
- Al filtrar por Pokemon, tener sumo cuidado con los campos **[nro_pokedex, nombre, tipo]**, ya que estan estrechamente relacionados, todos los nro_pokedex=1 tendran por nombre=Bulbasaur, con lo cual si se quisiera filtrar por [nro_pokedex, nombre] = [1, Charmander] resultara en un conjunto vacio. Lo mismo ocurre con el campo **tipo** para cualquiera de los dos anteriores. Por ejemplo: 
- Al filtrar por [nombre,tipo] = [Charmander,Veneno] --> **resultado: conjunto vacio**, esto ocurre ya que los campos de "Charmander" estan definidos como: [nro_pokedex, nombre, tipo] = [7,Charmander,Fuego]

# <ins> Filters: Pokemon </ins> 
| **Field**     |        **Type**	     | **Example** | **Filter Type** |
|---------------|------------------------|-------------|------------------|
|       `id`      |        `Int(11) > 0`     | `?id=4`|   `KEY`|
|   `nro_pokedex` |        `Int(1) > 0`  	 | `?nro_pokedex=25`| `Secundario`|
|     `nombre`    |        `Int(30) > 0`   	 | `?nombre=Pikachu`| `Secundario`|
|      `tipo`     |        `Varchar(20)`      | `?tipo=Electrico`|`Opcional`|
| `fecha_captura` |        `Date`         	 | `?fecha_captura=2020-03-04 16:26:43`|`Opcional`|
|     `peso`      |        `Int(11) > 0`       | `?peso=60`| `Opcional`|
| `id_entrenador` |        `Int(12) > 0`       | `?id_entrenador=4`| `Opcional`|

# <ins> Filters: Aprendizaje</ins> 
| **Field**       |        **Type**	     | **Example** | **Filter Type** |
|-----------------|----------------------|-------------|---------------------|
|   `id_pokemon`    |        `Int(11) > 0`     | `?id_pokemon=4`         |   `KEY`|
|  `id_movimiento`  |        `Int(11) > 0`  	 | `?id_movimiento=1`    |   `KEY` |
|`nivel_aprendizaje`|        `Int(11) > 0`   	 | `?nivel_aprendizaje=1`| `Secundario`|

# <ins> Filters: Movimiento </ins> 
| **Field**     		|        **Type**	     | **Example** | **Filter Type** |
|-----------------------|------------------------|-------------|------------------|
|`id_movimiento`    	  	|        `Int(11) > 0`     	 | `?id_movimiento=1`|   `KEY`  |
|`nombre_movimiento` 		|        `Varchar(50)`		  	 | `?nombre_movimiento=Impactrueno`| `Secundario`   |
|`tipo_movimiento`    	|        `Varchar(20)`		   	 | `?tipo_movimiento=Electrico`| `Opcional` |
|`poder_movimiento`     	|        `Int(11) > 0`         	 | `?poder_movimiento=40`| `Opcional` |  
|`presicion_movimiento` 	|        `Int(11) > 0`       | `?presicion_movimiento=90`|`Opcional`  |
|`descripcion_movimiento` |        `Text`		     | `?descripcion_movimiento=Un pequeño rayo que golpea al oponente.`|`Opcional` |

### `Limite`
- Si se especifica la query param: ?limit nos permitira obtener un listado limitado de pokemons. 
- Es posible especificar la cantidad de elementos que se desean obtener en la consulta. Si se declara el filtro **?limit** y no se le asigna un valor, por default retorna los primeros 10 elementos de la consulta, en cambio si se le asigna un valor, obtendra los primeros **i** elementos del resultado de la busqueda.  <br/>
    Endpoint: 	
        * api/aprendizaje?limit		(limit=10 default)
        * api/aprendizaje?limit=**i**

### `Paginacion` 

|**Parameter**	|**Type**	|**Example**	|			 **Description** 		   	 |**Case-Insensitive-Param**|
|---------------|-----------|---------------|----------------------------------------|--------------------------|
| `page ó p`	    |`Int`		|	`page=1`		|		`Obtiene la primer pagina`		 | 		`ACEPTA`|
| `limit ó l`	    |`Int`		|	`limit=10`	|`Obtiene los primeros 10 items por pagina`| 		`ACEPTA`|

- Si se especifica la query ?page junto a ?limit obtendremos la posibilidad de paginar. Si solo se declara:
    - ?page=i, se obtendra un paginado de i elementos por pagina
    - ?page por defecto muestra los primeros 10 elementos de la primer pagina

    Endpoint:                                **[elementos,pagina]**
    - api/aprendizaje?page 					   		**[10,1] default** 	
    - api/aprendizaje?page=**p** 		           	**[10,p]**
    - api/aprendizaje?page=**p**&&limit=**e**    	 **[e,p]**

[Notas]: Si no se declara el orden de los elementos, por defecto la informacion llegara en **orden acendente** por **[id_pokemon,id_movimiento,nivel_aprendizaje]**. 

Ejemplos: <br/>
    Si desea paginar debera ir modificando el paramero ?page en cada peticion:
    Para mostrar los primeros 'n' elementos:(page=1), para mostrar los segundos 'n+limit' elementos: (page=2),..,(page_m). <br/>
    
    El limite nos define la cantidad de filas/pokemons que se listaran, si toma el valor limit=5, como por defecto siempre la página inicia en 1 se mostraran los primeros 5 Pokémons junto a toda su información, si el limit=5 y page=2 se listaran los siguientes 5 pokemons, es decir los pokemons que se encuentran entre (pokemon[6] ,pokemon[10]) respecto de la consulta que se especifico.


## GET por id: <br/>
**`api/aprendizaje/:id_pok/:id_mov`** <br/>
Ejemplos: [validos]: <br/>
* api/aprendizaje/4/1     
* api/aprendizaje/1/5   [Existence_Warning]


## PATCH: <br/>
**`api/aprendizaje/:id_pok/:id_mov`**	 <br/>
(Se pueden modificar 1 o todos los campos(leer advertencia))  <br/>
Ejemplo: [valido]: <br/>
* api/aprendizaje/1/1 
```json
    "body:"{		
        "FK_id_pokemon":2,
        "FK_id_movimiento":10,
        "nivel_aprendizaje":10		 
    }
```

**[Advertencia]: Modificar en simultaneo los campos de [FK_id_pokemon, FK_id_movimiento] resulta en una combinacion de eliminar,insertar. Es decir si modificamos la relacion que vincula "api/aprendizaje/1/1" por los elementos del body:**
- Eliminara el vinculo entre  [FK_id_pokemon, FK_id_movimiento] = [1,1]
- Insertando un nuevo vinculo [FK_id_pokemon, FK_id_movimiento] = [2,10] y modificando el nivel de aprendizaje por "nivel_aprendizaje":10	
    - Antes: [FK_id_pokemon, FK_id_movimiento, nivel_aprendizaje] = [1,1,nivel_actual]
    - Despues: [FK_id_pokemon, FK_id_movimiento, nivel_aprendizaje] = [2,10,10]


## POST: 
**`api/aprendizaje`** <br/>
(Para insertar se deben ingresar todos los campos)<br/>
Ejemplo: [valido]:<br/>
**api/aprendizaje**
```json
    "body:"{
        "id_pokemon":11,
        "id_movimiento":4,
        "nivel_aprendizaje":12
        }
```

## **Tabla Movimiento** <br/>
[Estructura de Movimiento]: <br/>
**Response:** <br/> 
```json
    {
        "id_movimiento": 5,
        "nombre_movimiento": "Hidrobomba",
        "tipo_movimiento": "Agua",
        "poder_movimiento": 110,
        "precision_movimiento": 80,
        "descripcion_movimiento": "Un fuerte ataque de agua con alta potencia pero baja precisión."
    }
```

## GET All: <br/>
**`api/movimiento`** <br/>
[Estructura de Movimientos]: <br/>
**Response:**<br/>
```json
    [
        "Movimiento1",
        "Movimiento2",
        "...",
        "Movimiento6",
        "Movimiento7"
        "...",
        "Movimiento12"
    ]
```


## GET por id: <br/>
**`api/movimiento/:id`**<br/>
(Devuelve la informacion del movimiento con id = :id) <br/>


## PATCH: <br/>
**`api/movimiento/:id`**  <br/>


## PUT: <br/>
**`api/movimiento`** <br/>


## **Tabla Pokemon**  <br/>
[Estructura de Pokemon]: <br/>
**Response:**: <br/>
```json
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
```


## GET All: <br/>
**`api/pokemon`** <br/>
[Estructura de Pokemon]: <br/>
**Response:** <br/>
```json
    [
        "Pokemon1",
        "Pokemon2",
        "...",
        "Pokemon11",
        "...",
        "Pokemon25"
    ]
```


## GET por id: <br/>
**`api/pokemon/:id`** <br/>


## PATCH: <br/>
**`api/pokemon/:id`** <br/>


## PUT: <br/>
**`api/pokemon`** <br/>


**[Ejemplos de algunas combinaciones posibles:]**

### <ins>Filtros y Ordenamientos:</ins>
* api/aprendizaje?nombre_movimiento=Impactrueno&sort_peso=DESC&nombre=PikaChU
* api/aprendizaje?sort_nombre_movimiento&limit=2&nombre=PikaChU&page=2
* api/aprendizaje?id_pokemon=2&nombre=charmander
* api/aprendizaje?sort_nombre=Desc&id_movimiento=1

### <ins>Fechas:</ins>
* api/aprendizaje?sort_id_entrenador&fecha_captura=6/4/2010 (fechas: dia/mes/año)
* api/aprendizaje?nombre=Jolteon&fecha_captura=17/11/2024

### <ins>Descripcion:</ins>
* api/aprendizaje?descripcion_movimiento=un fuerte ataq
* api/aprendizaje?descripcion_movimiento=10%



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
