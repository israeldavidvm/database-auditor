
# data-auditor

[Readme version in English](./README-EN.md)

## data-auditor: Lleva tu diseño de bases de datos al siguiente nivel

Data-auditor es una herramienta de software libre diseñada para mejorar significativamente la calidad de tus diseños de bases de datos. Al realizar una serie de validaciones rigurosas, como la comprobación de las formas normales y la verificación de la propiedad de concatenación no aditiva, data-auditor te ayuda a identificar y corregir potenciales problemas en tu esquema de datos.

## Licencia

Este código tiene licencia bajo la licencia pública general de GNU versión 3.0 o posterior (LGPLV3+). Puede encontrar una copia completa de la licencia en https://www.gnu.org/licenses/lgpl-3.0-standalone.htmlalone.html0-standalone.html

## Caracteristicas ¿Qué te ofrece data-auditor?

data-auditor te ofrece un conjunto completo de herramientas para evaluar y mejorar la calidad de tu diseño de base de datos. Algunas de sus principales características incluyen:

- Validación de formas normales: Verifica si tu diseño cumple con las primeras tres formas normales, lo que es esencial para evitar la redundancia y las anomalías de actualización.

- Comprobación de la propiedad de concatenación no aditiva: Identifica posibles problemas de diseño que podrían llevar a resultados inesperados en las consultas.

- Análisis de dependencias funcionales: Te ayuda a comprender las relaciones entre los atributos de tus tablas.

## Challenges conquered / Desafíos Conquistados

## Features to implement / Caracteristicas a implementar

- Valida que los nombres de tablas y atributos ingresados como entrada sean validos
- Soporte a nombres a atributos, tablas, fk y pk no convencionales

## Requirements Engineering / Ingenieria de Requerimientos

###  High Level or user requirements / Requerimientos de Alto Nivel o de Usuario

#### Functional requirements / Requerimientos funcionales
#### Non -functional requirements / Requerimientos no funcionales

### Low Level or System Requirements / Requerimientos de Bajo Nivel o de Sistema

#### Functional requirements / Requerimientos funcionales
#### Non -functional requirements / Requerimientos no funcionales


## Software Design / Diseño de Software

### Perspectiva Estructural

#### Vista Logica de la Arquitectura del software 

En el siguiente diagrama de clases se veran las abstracciones clave en el sistema, sus interaciones  responsabilidades.


``` mermaid

---
title: database auditor
---
classDiagram
    
    class DatabaseAuditor{
    
    }

    note for DatabaseAuditor "Busca ser el contexto para las distintas estrategias usadas
    y un medio que encapsula funciones utilitarias 
    comunes para todos los algoritmos"

    DatabaseAuditor ..> DatabaseSchemaGenerator

    class DatabaseSchemaGenerator{
        <<Abstract>>
        +databaseAuditor
        +generate()
    }

    note for DatabaseSchemaGenerator "Proporciona la interfaz estrategia 
    que es común a todas las estrategias concretas 
    para la generacion de los 
    esquemas de la base de datos"

    DatabaseSchemaGenerator ..> SchemaFromDBUsingName
    DatabaseSchemaGenerator ..> SchemaFromJSON

    class SchemaFromJSON{
        +databaseAuditor
        +generate()
    }

    class SchemaFromDBUsingName{
        +databaseAuditor
        +generate()
    }

    note for SchemaFromDBUsingName "Es una de las estrategias concretas 
    que genera los esquemas por medio de los nombres
     de las convenciones de nombres de la base de datos"

    DatabaseAuditor ..> ValidationAlgorithm

    note for ValidationAlgorithm "Proporciona la interfaz estrategia 
    que es común a todas las estrategias concretas 
    para la generacion de las 
    validacione de la base de datos"

    class ValidationAlgorithm{
        <<Abstract>>
        +execute()
    }

    ValidationAlgorithm <|-- NonAdditiveConcatenation

    class NonAdditiveConcatenation{
        +databaseAuditor
        +execute()
    }

    note for NonAdditiveConcatenation "Encapsula el Algoritmo 11.1 de Verificación 
de la propiedad de concatenación no aditiva propuesto por RAMEZ ELMASRI 
y SHAMKANT B. NAVATHE"
    
    DatabaseAuditor <.. Client

    class Client{

    }

```

### Perspectiva de comportamiento

#### DatabaseAuditor.generateMetaInfoJoinsClusters() process / Proceso de DatabaseAuditor.generateMetaInfoJoinsClusters()

``` mermaid
---
title: Proceso de SchemaFromDatabaseUsingName.generateMetaInfoJoinsClusters()
---
stateDiagram-v2
    getTables: obtener las tablas
    
    [*] --> getTables

    getTables --> foreachTables

    note left of foreachTables : "Esta actividad se ejecuta por cada tabla"
    state foreachTables {

        state if1 <<choice>> 

        [*] -->  relationtables=getTablesManyToManyRelationship(table)
        relationtables=getTablesManyToManyRelationship(table) --> if1
        if1 --> manyToManyRelationshipAnalysis  : relationtables!=null
        
        state manyToManyRelationshipAnalysis {

            state if2 <<choice>> 

            [*] --> if2
                
            if2 --> [*] : isRecursive(table)

            if2 --> addManyManyTable : else

            addManyManyTable: metaInfoClusterTables[last][]=manyToManytable

            addManyManyTable --> foreachRelationTables

            note left of foreachRelationTables : "Esta actividad se ejecuta por cada tabla que participa en la relacion mucho a mucho"

            state foreachRelationTables {
            
                addRelationTable: metaInfoClusterTables[last][]=relationtable

                [*] --> addRelationTable

                addRelationTable --> [*]
            
            }
        
            foreachRelationTables --> [*]

        }

        manyToManyRelationshipAnalysis -->  normalRelationshipAnalysis

        if1 --> normalRelationshipAnalysis : relationtables==null    

        state normalRelationshipAnalysis {

            [*] --> fks=getFKByTable(table)
            
            fks=getFKByTable(table) --> excludeManyToManyFk

            state if3 <<choice>> 
            excludeManyToManyFk --> if3

            if3 --> [*] : else

            addReferencingTable: metaInfoClusterTables[last][]=referencingTable
            if3 --> addReferencingTable : fks!=null

            addReferencingTable  --> foreachFK
            note left of foreachFK : "Esta actividad se ejecuta por cada tabla que a la apunta una fk"
            state foreachFK {
            
                addReferencedTable : metaInfoClusterTables[]=referencedTable

                [*] --> addReferencedTable

                addReferencedTable --> [*]
            
            }
        
            foreachFK --> [*]

        }

        normalRelationshipAnalysis --> [*]

    }


```

#### SchemaFromDatabaseUsingName.generate() process / Proceso de SchemaFromDatabaseUsingName.generate()

``` mermaid
---
title: Proceso de SchemaFromDatabaseUsingName.generate()
---
stateDiagram-v2
    
    [*] --> getTables

    state getTables{

        state if2 <<choice>>

        [*]-->if2

        if2-->getTablesIncludeMode : mode==include
        getTablesIncludeMode-->[*] 

        if2-->getTablesExcludeMode : mode==exclude
        getTablesExcludeMode-->[*]

    }
    
    getTables --> foreachTables 

    note left of foreachTables : "Esta actividad se ejecuta por cada tabla"

    state foreachTables {

        [*] --> getColumnsFullyQualifiedForm
                    
        note left of getColumnsFullyQualifiedForm : "Si una columna no es una llave foranea columnName=pluralToSingular(tableName).columnName"

        getColumnsFullyQualifiedForm --> foreachColumn 

        note left of foreachColumn : "Esta actividad se ejecuta por cada columna"

        state foreachColumn {

            state if3 <<choice>> 

            [*]-->if3
            addColumnUniversalRelationship: "agregar Columna a la universalRelationship "
            addColumnTableDecomposition: "agregar Columna a la decomposition de la tabla "
            
            if3 --> addColumnUniversalRelationship : !columnInUniversalRelationship
            addColumnUniversalRelationship-->addColumnTableDecomposition

            if3 --> addColumnTableDecomposition : else
            addColumnTableDecomposition --> [*]
        
            }

            foreachColumn-->addTrivialFunctionalDependency
        
            state addTrivialFunctionalDependency{
                addPkFunctionalDependency: si existe una pk agregar pk como antecedente a la dependencia funcional y los demas atributos como consecuente
                [*]-->addPkFunctionalDependency
                addPkFunctionalDependency-->[*]
            }

            addTrivialFunctionalDependency --> [*]

        }
```

## Documentacion

El paquete data-auditor permite realizar una serie de validaciones y mejoras a la calidad del diseño de una base de datos como la comprobacion de formas normales, verificación 
de la propiedad de concatenación no aditiva, etc.

### Convenciones usadas durante la docuemntacion

Convenciones de notacion para la gramatica:

Los <> se utilizan para rodear un simbolo no terminal

El ::= se utiliza para reglas de produccion

Los simbolos no terminales se expresan como una cadena o caracteres normales

El siguiente grupo de pares de simbolos, se deben utilizar junto a las expresiones de la siguiente forma: el primero en cada pareja se escribe como sufijo despues de la expresion y el segundo rodea la expresion. 

El ? o [] indican que la expresion es opcional

El * o {} indica que la expresion se repite 0 o mas veces

El + indica que la expresion se repite 1 o mas veces

Si se quiere usar uno de los caracteres anteriores se debe de anteceder \ con 

### Generacion de esquemas

Para que los algoritmos funcionen se debe de generar una representacion de la base de datos 
sobre la cual aplicar dichos algoritmos

Lamentablemente algunos SGBD, dan soporte a los estadares de sql de forma diferente por lo que no existe una algoritmo universal que funcione perfectamente para todos los SGBD.

De manera que el software se diseño tomando en cuenta la posibilidad de utilizar diferentes algoritmos. Mas concretamente se utilizara el patron strategy para permitir el intercambio de algoritmos

Las estructuras responsables para esto seran las siguientes:

#### DatabaseAuditor 
Busca ser el contexto para las distintas estrategias usadas y un medio que encapsula funciones utilitarias comunes para todos los algoritmos

#### DatabaseSchemaGenerator 
proporciona la interfaz estrategia 
que es común a todas las estrategias concretas 
para la generacion de los 
esquemas de la base de datos

#### SchemaFromDBUsingName 
Es una de las estrategias concretas  que genera los esquemas por medio de los nombres
de las columnas de la base de datos

Si bien se pudo usar el conjunto de vistas de information_schema para determinar algunas de las estructuras de la base de datos

Se descubrio que en postgresql se suelen generar joins aditivos 

Por ejemplo si se quisiera saber si una columna en la information_schema.key_column_usage es una PRIMARY KEY, FOREIGN KEY, etc, deberia usarse la consulta

```sql
SELECT kcu.column_name,kcu.table_name,tc.constraint_name,tc.constraint_type 
            FROM 
                information_schema.table_constraints tc
            JOIN
                information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
```    
Sin embargo si observamos detenidamente dichos resultados se ven afectados por un join aditivo

Para el caso particular de tener
```sql
SELECT column_name,table_name FROM information_schema.key_column_usage WHERE table_name ~ '^insight_taxonomy$'
```

que arroja resultados de esta forma

![Information_schema.key_column_usage_where_table_name_insight_taxonomy](images/key_column_usage_where_table_name_insight_taxonomy.png)

y un 
```sql
SELECT tc.constraint_name
            FROM 
                information_schema.table_constraints tc               
            WHERE 
				tc.constraint_name ~ '^taxonomy_id_fkey$'
```
que arroja resultados de esta forma

![table_constraints_where_taxonomy_id_fkey](images/table_constraints_where_taxonomy_id_fkey.png)

se tiene que el

```sql
SELECT kcu.column_name,kcu.table_name,tc.constraint_name,tc.constraint_type 
            FROM 
                information_schema.table_constraints tc
            JOIN
                information_schema.key_column_usage kcu
                    ON tc.constraint_name = kcu.constraint_name
            WHERE 
				kcu.table_name ~ '^insight_taxonomy$'

```

genera los siguientes resultados que reflejan el join aditivo

![aditiveJoin.png](images/aditiveJoin.png)

##### Convenciones de nombres usada para la identificacion de elementos

##### Llaves Primarias
Todo atributo de nombre id 

###### Ejemplos
id

##### Llaves Foraneas
Todo atributo que posee la siguiente forma 
```
<nombreTablaSigular>_[rol]_id 

Donde [rol] sirve para identificar a la entidad en las relaciones recursivas

```

Coincide con la siguiente expresion regular
```
^[a-zA-Z0-9ñ]+_?[a-zA-Z0-9ñ]*_id$
```
###### Ejemplos
user_id
taxonomy_child_id
taxonomy_parent_id

### Ejemplos de uso

#### Almacenar la url de las imagenes en la bd:

Para almacenar una imagen responsive en la base de datos de una aplicacion laravel  se recomienda usar 

```
NameHelper::generateLaravelConvetionalResponsiveImageDirUrl($imageName)
```

Lo que generara una url de imagen como 
```/storage/images/imagen/```

Para el caso de imagenes no responsivas

```
NameHelper::generateLaravelConvetionalImageUrl($imageName)
```

Lo que generara una url de imagen como 
```/storage/images/imagen/imagen.jpg```

#### Recuperar imagenes en la bd:

Notese que las imagenes responsives estan almacenadas en un directorio

De manera que los nombres para cada una de las imagenes responsive deben obtenerse por medio de

```
NameHelper::generateResponsiveImageUrls($imageName, $baseUrl);
```

o 

```
NameHelper::generateConvetionalResponsiveImageUrls($imageName,$baseUrl)
```

O 
```
NameHelper::generateLaravelConvetionalResponsiveImageUrls($imageName)
```
#### Mas ejemplos de uso
```
Probando la salida de los metodos con

$fileLocator='/imagen.png'
$baseUrl='/cachapa/'

NameHelper::generateLaravelConvetionalResponsiveImageUrls('/imagen.png')=[
/storage/images/imagen/imagen.png
/storage/images/imagen/360-imagen.png
/storage/images/imagen/720-imagen.png
/storage/images/imagen/1080-imagen.png
/storage/images/imagen/1440-imagen.png
/storage/images/imagen/1800-imagen.png
/storage/images/imagen/2160-imagen.png
/storage/images/imagen/2880-imagen.png
/storage/images/imagen/3600-imagen.png
/storage/images/imagen/4320-imagen.png
]
NameHelper::generateLaravelConvetionalResponsiveImageDirUrl('/imagen.png')=/storage/images/imagen
NameHelper::generateLaravelConvetionalImageUrl('/imagen.png')=/storage/images/imagen/imagen.png
NameHelper::generateLaravelConvetionalImagePath('/imagen.png')=/images/imagen/imagen.png
NameHelper::generateConvetionalResponsiveImageUrls('/imagen.png','/cachapa/')=[
/cachapa/imagen/imagen.png
/cachapa/imagen/360-imagen.png
/cachapa/imagen/720-imagen.png
/cachapa/imagen/1080-imagen.png
/cachapa/imagen/1440-imagen.png
/cachapa/imagen/1800-imagen.png
/cachapa/imagen/2160-imagen.png
/cachapa/imagen/2880-imagen.png
/cachapa/imagen/3600-imagen.png
/cachapa/imagen/4320-imagen.png
]
NameHelper::generateConvetionalImageUrl('/imagen.png','/cachapa/')=/cachapa/imagen/imagen.png
NameHelper::generateResponsiveImageUrls('/imagen.png','/cachapa/')=[
/cachapa/imagen.png
/cachapa/360-imagen.png
/cachapa/720-imagen.png
/cachapa/1080-imagen.png
/cachapa/1440-imagen.png
/cachapa/1800-imagen.png
/cachapa/2160-imagen.png
/cachapa/2880-imagen.png
/cachapa/3600-imagen.png
/cachapa/4320-imagen.png
]
NameHelper::generateConvetionalImageDirUrl('/imagen.png','/cachapa/')=/cachapa/imagen
NameHelper::generateImageUrl('/imagen.png','/cachapa/')=/cachapa/imagen.png
NameHelper::generateResponsiveImageNames('/imagen.png')=[
/imagen.png
360-/imagen.png
720-/imagen.png
1080-/imagen.png
1440-/imagen.png
1800-/imagen.png
2160-/imagen.png
2880-/imagen.png
3600-/imagen.png
4320-/imagen.png
]
NameHelper::transformNameToUrlName('/imagen.png')=imagen.png
NameHelper::getFileOrDirName('/imagen.png')=imagen.png
NameHelper::getFileOrDirNameWithoutExt('/imagen.png')=imagen
NameHelper::getExtOfFile('/imagen.png')=png

Probando la salida de los metodos con

$fileLocator='/imagen/'
$baseUrl='/cachapa/'

$imageName='imagen' de generateResponsiveImageNames pareciera no tener una extension
NameHelper::generateLaravelConvetionalResponsiveImageDirUrl('/imagen/')=/storage/images/imagen
NameHelper::generateLaravelConvetionalImageUrl('/imagen/')=/storage/images/imagen/imagen
NameHelper::generateLaravelConvetionalImagePath('/imagen/')=/images/imagen/imagen
$imageName='imagen' de generateResponsiveImageNames pareciera no tener una extension
NameHelper::generateConvetionalImageUrl('/imagen/','/cachapa/')=/cachapa/imagen/imagen
$imageName='imagen' de generateResponsiveImageNames pareciera no tener una extension
NameHelper::generateConvetionalImageDirUrl('/imagen/','/cachapa/')=/cachapa/imagen
NameHelper::generateImageUrl('/imagen/','/cachapa/')=/cachapa/imagen
$imageName='/imagen/' de generateResponsiveImageNames pareciera no tener una extension
NameHelper::transformNameToUrlName('/imagen/')=imagen
NameHelper::getFileOrDirName('/imagen/')=imagen
NameHelper::getFileOrDirNameWithoutExt('/imagen/')=imagen
$fileLocatorName='/imagen/' de getExtOfFile pareciera no tener una extension
```

### Make a donation. Your contribution will make a difference.
[![ko-fi](https://ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/israeldavidvm)
[![Paypal](https://img.shields.io/badge/Paypal-@israeldavidvm-0077B5?style=for-the-badge&logo=paypal&logoColor=white&labelColor=101010)](https://paypal.me/israeldavidvm)
[![Binance](https://img.shields.io/badge/Binance_ID-809179020-101010?style=for-the-badge&logo=binancel&logoColor=white&labelColor=101010)](https://www.binance.com/activity/referral-entry/CPA?ref=CPA_004ZGH9EIS)

### Find me on:
[![GITHUB](https://img.shields.io/badge/Github-israeldavidvm-gray?style=for-the-badge&logo=github&logoColor=white&labelColor=101010)](https://github.com/israeldavidvm)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-israeldavidvm-0077B5?style=for-the-badge&logo=linkedin&logoColor=white&labelColor=101010)](https://www.linkedin.com/in/israeldavidvm/)
[![Twitter](https://img.shields.io/badge/Twitter-@israeldavidvm-1DA1F2?style=for-the-badge&logo=twitter&logoColor=white&labelColor=101010)](https://twitter.com/israeldavidvm)
[![Facebook](https://img.shields.io/badge/Facebook-israeldavidvm-1877F2?style=for-the-badge&logo=facebook&logoColor=white&labelColor=101010)](https://www.facebook.com/israeldavidvm)
[![Instagram](https://img.shields.io/badge/Instagram-@israeldavidvmv-gray?style=for-the-badge&logo=instagram&logoColor=white&labelColor=101010)](https://www.instagram.com/israeldavidvm/)
[![TikTok](https://img.shields.io/badge/TikTok-@israeldavidvm-E4405F?style=for-the-badge&logo=tiktok&logoColor=white&labelColor=101010)](https://www.tiktok.com/@israeldavidvm)
[![YouTube](https://img.shields.io/badge/YouTube-@israeldavidvm-FF0000?style=for-the-badge&logo=youtube&logoColor=white&labelColor=101010)](https://www.youtube.com/channel/UCmZLFpEPNdwpJOhal0wry7A)

## Technologies used / Tecnologias usadas

[![PHP](https://img.shields.io/badge/php-blue?logo=php&style=for-the-badge&logoColor=blue&labelColor=gray)]() 
