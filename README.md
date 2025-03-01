<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [data-auditor](#data-auditor)
  - [¡Optimiza tu diseño de bases de datos con data-auditor!](#%C2%A1optimiza-tu-dise%C3%B1o-de-bases-de-datos-con-data-auditor)
  - [Licencia](#licencia)
  - [Caracteristicas ¿Qué te ofrece data-auditor?](#caracteristicas-%C2%BFqu%C3%A9-te-ofrece-data-auditor)
  - [Challenges conquered / Desafíos Conquistados](#challenges-conquered--desaf%C3%ADos-conquistados)
  - [Features to implement / Caracteristicas a implementar](#features-to-implement--caracteristicas-a-implementar)
  - [Planning, Requirements Engineering and risk management / Planeacion, Ingenieria de Requerimientos y gestion del riesgo](#planning-requirements-engineering-and-risk-management--planeacion-ingenieria-de-requerimientos-y-gestion-del-riesgo)
  - [Software Design / Diseño de Software](#software-design--dise%C3%B1o-de-software)
    - [Perspectiva Estructural](#perspectiva-estructural)
      - [Vista Logica de la Arquitectura del software](#vista-logica-de-la-arquitectura-del-software)
    - [Perspectiva de comportamiento](#perspectiva-de-comportamiento)
      - [SchemaFromDatabaseUsingName.generateJoinsClusters process / Proceso de SchemaFromDatabaseUsingName.generateJoinsClusters](#schemafromdatabaseusingnamegeneratejoinsclusters-process--proceso-de-schemafromdatabaseusingnamegeneratejoinsclusters)
      - [SchemaFromDatabaseUsingName.generate() process / Proceso de SchemaFromDatabaseUsingName.generate()](#schemafromdatabaseusingnamegenerate-process--proceso-de-schemafromdatabaseusingnamegenerate)
  - [Verification and Validation / Validacion y Verificacion](#verification-and-validation--validacion-y-verificacion)
    - [Formal validation / Validacion Formal](#formal-validation--validacion-formal)
      - [getFunctionalDependenciesForBCNFInTable](#getfunctionaldependenciesforbcnfintable)
        - [BCNF Definition / Definicion BCNF](#bcnf-definition--definicion-bcnf)
        - [Closing a set of Functional Dependencies / Clasura de un conjunto de dependencias funcionales](#closing-a-set-of-functional-dependencies--clasura-de-un-conjunto-de-dependencias-funcionales)
        - [Inference Rules for Functional Dependencies / Reglas de inferencia para las dependencias funcionales](#inference-rules-for-functional-dependencies--reglas-de-inferencia-para-las-dependencias-funcionales)
          - [Regla reflesiva](#regla-reflesiva)
          - [Reglas transitiva, de descomposicion y union](#reglas-transitiva-de-descomposicion-y-union)
          - [Reglas de de aumento y pseudo-transitividad](#reglas-de-de-aumento-y-pseudo-transitividad)
  - [Documentacion](#documentacion)
    - [Convenciones usadas durante la docuemntacion](#convenciones-usadas-durante-la-docuemntacion)
    - [Generacion de esquemas](#generacion-de-esquemas)
      - [DatabaseAuditor](#databaseauditor)
      - [DatabaseSchemaGenerator](#databaseschemagenerator)
      - [SchemaFromDBUsingName](#schemafromdbusingname)
        - [Convenciones de nombres usada para la identificacion de elementos](#convenciones-de-nombres-usada-para-la-identificacion-de-elementos)
          - [Llaves Primarias](#llaves-primarias)
          - [Ejemplos](#ejemplos)
          - [Llaves Foraneas](#llaves-foraneas)
          - [Ejemplos](#ejemplos-1)
    - [Validacion de los esquemas de base de datos](#validacion-de-los-esquemas-de-base-de-datos)
      - [ValidationAlgorithm](#validationalgorithm)
        - [VerificationNonAdditiveConcatenation](#verificationnonadditiveconcatenation)
        - [VerificationBCNF](#verificationbcnf)
    - [Uso](#uso)
      - [Requisitos](#requisitos)
        - [Instalacion](#instalacion)
          - [Como usuario](#como-usuario)
          - [Como biblioteca (Solo si quieres crear un programa que use la libreria)](#como-biblioteca-solo-si-quieres-crear-un-programa-que-use-la-libreria)
        - [Archivo .env (esto es necesario cuando se quiere generar un esquema a partir de ña base de datos el comportamiento por defecto)](#archivo-env-esto-es-necesario-cuando-se-quiere-generar-un-esquema-a-partir-de-%C3%B1a-base-de-datos-el-comportamiento-por-defecto)
      - [Uso desde la interfaz de linea de comandos](#uso-desde-la-interfaz-de-linea-de-comandos)
    - [Make a donation. Your contribution will make a difference.](#make-a-donation-your-contribution-will-make-a-difference)
    - [Find me on:](#find-me-on)
  - [Technologies used / Tecnologias usadas](#technologies-used--tecnologias-usadas)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


# data-auditor

[Readme version in English](./README-EN.md)

## ¡Optimiza tu diseño de bases de datos con data-auditor!
¿Quieres asegurarte de que tu base de datos esté libre de redundancias, anomalías y problemas de diseño? Con data-auditor, obtén las herramientas necesarias para validar formas normales, analizar dependencias funcionales y garantizar un diseño robusto y eficiente. ¡Prueba nuestra interfaz de línea de comandos y lleva tu base de datos al siguiente nivel!

## Licencia

Este código tiene licencia bajo la licencia pública general de GNU versión 3.0 o posterior (LGPLV3+). Puede encontrar una copia completa de la licencia en https://www.gnu.org/licenses/lgpl-3.0-standalone.htmlalone.html0-standalone.html

## Caracteristicas ¿Qué te ofrece data-auditor?

data-auditor es una herramienta integral diseñada para evaluar y optimizar la calidad de tus diseños de bases de datos. Ofrece un conjunto de funcionalidades avanzadas que incluyen:

Validación de formas normales: Asegura que tu diseño cumpla con las primeras tres formas normales, minimizando la redundancia y evitando anomalías en las actualizaciones.

Comprobación de la propiedad de concatenación no aditiva: Detecta posibles problemas de diseño que podrían afectar los resultados de las consultas.

Análisis de dependencias funcionales: Facilita la comprensión de las relaciones entre los atributos de las tablas, permitiendo un diseño más robusto y eficiente.

Interfaz de línea de comandos: Proporciona una forma sencilla y directa de utilizar la librería, ideal para integración en flujos de trabajo automatizados.

Con data-auditor, podrás garantizar un diseño de base de datos sólido, eficiente y libre de errores comunes.

## Challenges conquered / Desafíos Conquistados

- Demostracion formal de algoritmos

## Features to implement / Caracteristicas a implementar
- Deteccion de errores en el diseño de la base de datos que afecten el funcionamiento de los algoritmos
- Valida que los nombres de tablas y atributos ingresados como entrada sean validos
- Soporte a nombres a atributos, tablas, fk y pk no convencionales
- Soporte a relaciones recursivas

## Planning, Requirements Engineering and risk management / Planeacion, Ingenieria de Requerimientos y gestion del riesgo

Estas secciones del proyecto se llevara por medio de un sitio en notion de forma que puedan ser facilmente accesibles por el personal no tecnico.

Solicita el link de acceso al personal autorizado

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
    validaciones de la base de datos"

    class ValidationAlgorithm{
        <<Abstract>>
        +execute()
    }

    ValidationAlgorithm <|-- NonAdditiveConcatenation
    ValidationAlgorithm <|-- VerificationBCNF


    class VerificationNonAdditiveConcatenation{
        +databaseAuditor
        +execute()
    }


    note for NonAdditiveConcatenation "Encapsula el Algoritmo 11.1 de Verificación 
de la propiedad de concatenación no aditiva propuesto por RAMEZ ELMASRI 
y SHAMKANT B. NAVATHE"

    class VerificationBCNF{
        +databaseAuditor
        +execute()
    }

    note for NonAdditiveConcatenation "Encapsula el Algoritmo que valida que cada 
    descomposicion posea la BCNF 
    en base a la definicion presentada 
    por RAMEZ ELMASRI y SHAMKANT B. NAVATHE"

    DatabaseAuditor <.. Client

    class Client{

    }

```

### Perspectiva de comportamiento

#### SchemaFromDatabaseUsingName.generateJoinsClusters process / Proceso de SchemaFromDatabaseUsingName.generateJoinsClusters

``` mermaid
---
title: Proceso de SchemaFromDatabaseUsingName.generateJoinsClusters
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

            addManyManyTable: joinsClusters[last][]=manyToManytable

            addManyManyTable --> foreachRelationTables

            note left of foreachRelationTables : "Esta actividad se ejecuta por cada tabla que participa en la relacion mucho a mucho"

            state foreachRelationTables {
            
                addRelationTable: joinsClusters[last][]=relationtable

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

            addReferencingTable: joinsClusters[last][]=referencingTable
            if3 --> addReferencingTable : fks!=null

            addReferencingTable  --> foreachFK
            note left of foreachFK : "Esta actividad se ejecuta por cada tabla que a la apunta una fk"
            state foreachFK {
            
                addReferencedTable : joinsClusters[]=referencedTable

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

## Verification and Validation / Validacion y Verificacion

### Formal validation / Validacion Formal

#### getFunctionalDependenciesForBCNFInTable

El objetivo de esta sección es demostrar que el algoritmo puede utilizarse para generar el conjunto de dependencias funcionales necesario para validar la BCNF (Forma Normal de Boyce-Codd).

El nuevo algoritmo que presentaremos a continuación se basa en la idea de que, para aplicar la verificación de BCNF en cada descomposición, podemos utilizar el conjunto de dependencias funcionales en el que tanto el antecedente como el consecuente son subconjuntos del conjunto de atributos de la descomposición, en lugar de utilizar la proyección del conjunto de dependencias funcionales para esa descomposición, ya que esta última opción resulta ser mucho más exigente en términos algorítmicos

Para ello es importante tener en cuenta que 

Un esquema de relación R está en BCNF si siempre que una dependencia funcional no trivial X → A se cumple en R, entonces X es una superclave de R. 

##### BCNF Definition / Definicion BCNF

![otra forma de expresar la bcnf](images/bcnf_definition.png)

y que el conjunto de dependencias funcionales para una descomposicion es la proyeccion del conjunto de dependencias de la relacion universal proyectado para una descomposicion.

Es decir

![Definition of the projection of F on Ri](images/definition_projection_F_Ri.png)

Donde F+ es la Clasura de un conjunto de dependencias funcionales

##### Closing a set of Functional Dependencies / Clasura de un conjunto de dependencias funcionales

Formalmente, el conjunto de todas las dependencias que incluyen F, junto con las dependencias que pueden inferirse de F, reciben el nombre de clausuras de F; está designada mediante F+.

##### Inference Rules for Functional Dependencies / Reglas de inferencia para las dependencias funcionales

Recordemos que las reglas de inferencia bien conocidas para las dependencias funcionales son
![inference rules for functional dependencies](images/inference_rules_functional_dependencies.png)

Habiendo dicho esto notese que para las reglas de inferencia sucede lo siguiente al ser aplicados sobre el algoritmo de bcnf

###### Regla reflesiva

Para el caso de la regla reflexiva  las dependencias funcionales generadas x->y requieren que x sea subconjunto de y de modo que solo genera dependencias triviales las cuales no se toman en cuenta en bcnf

###### Reglas transitiva, de descomposicion y union 

Para el caso de las reglas transitiva, de descomposicion y union el antecedente de las dependencias funcionales no cambia de manera que si el antecedente es super clave la descomposicion cumplira la regla de BCNF  en caso de que no sea super clave la descomposicion no cumplira la regla de BCNF es decir las descomposiciones inferidas con estas reglas no afectaran el resultado

###### Reglas de de aumento y pseudo-transitividad

Para el caso de las reglas de de aumento y pseudo-transitividad sucede que el antecedente X se une con otro conjunto W o Z  en cualquiera de los casos sucede que:
Si x es super clave solo se podran inferir reglas en las que el antecedente siga siendo super clave
Si x no es super clave se podran inferir reglas que sean super claves pero en caso de que x no sea super clave ya sabremos que por la definicion de BCNF esta forma no se cumple.

En conclusion:
Para el caso de la validacion de la BCNF podemos usar el


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

###### Llaves Primarias
Todo atributo de nombre id 

###### Ejemplos
id

###### Llaves Foraneas
Todo atributo que posee la siguiente forma 
```
<nombreTablaSigular>[_<rol>]_id 

Donde [rol] sirve para identificar a la entidad en las relaciones recursivas

```

Coincide con la siguiente expresion regular
```
^[a-zA-Z0-9ñ]+(?:_[a-zA-Z0-9ñ]+)?_id$
```
###### Ejemplos
- user_id
- taxonomy_child_id
- taxonomy_parent_id

### Validacion de los esquemas de base de datos

El objetivo de esta libreria es proporcionar validaciones para los esquemas de la base de datos y para ello se utilizaran las siguientes estructuras:

#### ValidationAlgorithm
Proporciona la interfaz estrategia que es común a todas las estrategias concretas para la generacion de las validaciones de la base de datos

##### VerificationNonAdditiveConcatenation

Encapsula el Algoritmo 11.1 de Verificación  de la propiedad de concatenación no aditiva propuesto por RAMEZ ELMASRI y SHAMKANT B. NAVATHE

##### VerificationBCNF

Encapsula el Algoritmo que valida que cada  descomposicion posea la BCNF  en base a la definicion presentada  por RAMEZ ELMASRI y SHAMKANT B. NAVATHE

Para el algoritmo se utilizar el conjunto de dependencias funcionales no triviales en el que tanto el antecedente como el consecuente son subconjuntos del conjunto de atributos de la descomposición, en lugar de utilizar el conjunto de dependencias no triviales en la proyección del conjunto de dependencias funcionales para esa descomposición esto debido a que para fines del algoritmo para verificar la BCNF los conjuntos funcionan de forma equivalente.

La demostracion formal de dicha afirmacion se encuentra en el README.md del paquete database-auditor.

### Uso

#### Requisitos 

##### Instalacion 

###### Como usuario

composer install israeldavidvm/database-auditor

composer global require israeldavidvm/database-auditor

composer require israeldavidvm/database-auditor


###### Como biblioteca (Solo si quieres crear un programa que use la libreria)
composer require israeldavidvm/database-auditor

##### Archivo .env (esto es necesario cuando se quiere generar un esquema a partir de ña base de datos el comportamiento por defecto)

Establece una configuracion en el archivo .env. como la siguiente

```

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=<DatabaseName>
DB_USERNAME=<UserName>
DB_PASSWORD=<password>

```

#### Uso desde la interfaz de linea de comandos 

Para poder usar el programa solo necesitaras un archivo .env con la configuracion de tu base de datos como se especifico anteriormente y ejecutar el comando

**Si es incluido en un proyecto por medio de require con el global (composer global require israeldavidvm/database-auditor)**

```~/.config/composer/vendor/bin/database-auditor  app:audit-database [<validationAlgorithms> [<databaseSchemaGeneratorConfig>]]```

**Si es incluido en un proyecto por medio de require sin el global (composer require israeldavidvm/database-auditor)**

```./vendor/bin/database-auditor  app:audit-database [<validationAlgorithms> [<databaseSchemaGeneratorConfig>]]```

**Si es instalado por medio de install o se parte de la raiz del proyecto (composer install israeldavidvm/database-auditor)**

```composer audit-database [<validationAlgorithms> [<databaseSchemaGeneratorConfig>]]```

Description:
  Este comando te permite realizar una serie de validacionesen tu base de datos redirige la salida para pasar la informacion a un archivo 

Arguments:
  validationAlgorithms           Valor de los tipos de algoritmo de validacion a aplicar separados por coma (,) Ejemplo VerificationBCNF,VerificationNonAdditiveConcatenation [default: "VerificationBCNF,VerificationNonAdditiveConcatenation"]
  databaseSchemaGeneratorConfig  Cadena que especifica el databaseSchemaGenerator y su configuracionDonde la cadena tiene un formato como el siguiente<databaseSchemaGenerator>|<config>Donde <databaseSchemaGenerator> es el Valor del tipo de generador de esquema de base de datoscomo por ejemplo SchemaFromDatabaseUsingNamey <config> es la configuracion del generador de esquema de base de datos que depemndera del tipo para el caso de SchemaFromDatabaseUsingName tiene el formato <mode>|<tables>|<path> [default: "SchemaFromDatabaseUsingName|exclude|users,migrations,password_resets,failed_jobs,personal_access_tokens,taxonomy_taxonomy|./.env"]

Options:
  -h, --help                     Display help for the given command. When no command is given display help for the list command
      --silent                   Do not output any message
  -q, --quiet                    Only errors are displayed. All other output is suppressed
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug


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
