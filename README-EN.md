<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Data-Auditor](#data-auditor)
  - [Optimize your database design with Data-Auditor!](#optimize-your-database-design-with-data-auditor)
  - [License](#license)
  - [Characteristics What does Data-Auditor offer you?](#characteristics-what-does-data-auditor-offer-you)
  - [Challenges conquered / conquered challenges](#challenges-conquered--conquered-challenges)
  - [features to implement / characteristics to be implemented](#features-to-implement--characteristics-to-be-implemented)
  - [Planning, Requirements Engineering and Risk Management / Planning, Requirements Engineering and Risk Management](#planning-requirements-engineering-and-risk-management--planning-requirements-engineering-and-risk-management)
  - [Design Software / Software Design](#design-software--software-design)
    - [Structural perspective](#structural-perspective)
      - [Logica view of software architecture](#logica-view-of-software-architecture)
  - [`` Mermaid](#-mermaid)
  - [Title: Database Auditor](#title-database-auditor)
    - [Behavior perspective](#behavior-perspective)
      - [Schemafromdatabaseusingname.Generatejoinscls Process / SchemaphromDatabaseusingName.](#schemafromdatabaseusingnamegeneratejoinscls-process--schemaphromdatabaseusingname)
  - [`` Mermaid](#-mermaid-1)
  - [Title: SchemafromDatabaseusingname process.](#title-schemafromdatabaseusingname-process)
      - [Schemafromdatabaseusingname.Generate () Process / SchemafromDatabaseusingname.Generate () process ()](#schemafromdatabaseusingnamegenerate--process--schemafromdatabaseusingnamegenerate--process-)
  - [`` Mermaid](#-mermaid-2)
  - [Title: SchemaphromDatabaseusingname process.Generate ()](#title-schemaphromdatabaseusingname-processgenerate-)
  - [Verification and Validation / Validation and Verification](#verification-and-validation--validation-and-verification)
    - [Formal Validation / Formal Validation](#formal-validation--formal-validation)
      - [Getfunctional Dependenciesforbcnfintable](#getfunctional-dependenciesforbcnfintable)
        - [BCNF Definition / BCNF definition](#bcnf-definition--bcnf-definition)
        - [CLOSING A SET OF FUNCTIONAL DEPENDENCIES / CLASURA OF A SET OF FUNCTIONAL DEPENDENCES](#closing-a-set-of-functional-dependencies--clasura-of-a-set-of-functional-dependences)
        - [Inference Rules for Functional Depending on Inference Rules for Functional Units](#inference-rules-for-functional-depending-on-inference-rules-for-functional-units)
          - [Reflesive rule](#reflesive-rule)
          - [Transitive rules, decomposition and union](#transitive-rules-decomposition-and-union)
          - [Increase and pseudo-transitivity rules](#increase-and-pseudo-transitivity-rules)
  - [Documentation](#documentation)
    - [Conventions used during documentation](#conventions-used-during-documentation)
    - [Generation of schemes](#generation-of-schemes)
      - [Databaseauditor](#databaseauditor)
      - [Database Schema Generator](#database-schema-generator)
      - [Schemafromdbusingname](#schemafromdbusingname)
        - [Name conventions used for the identification of elements](#name-conventions-used-for-the-identification-of-elements)
          - [Primary keys](#primary-keys)
          - [Examples](#examples)
          - [Foraneal keys](#foraneal-keys)
          - [Examples](#examples-1)
    - [Validation of database schemes](#validation-of-database-schemes)
      - [Validation Algorithm](#validation-algorithm)
        - [Verification](#verification)
        - [VerificationBCNF](#verificationbcnf)
    - [Use](#use)
      - [Requirements](#requirements)
        - [Facility](#facility)
          - [as a user](#as-a-user)
          - [as a library (only if you want to create a program that uses library)](#as-a-library-only-if-you-want-to-create-a-program-that-uses-library)
        - [File .env (this is necessary when you want to generate a scheme from the database the default behavior)](#file-env-this-is-necessary-when-you-want-to-generate-a-scheme-from-the-database-the-default-behavior)
      - [use from the command line interface](#use-from-the-command-line-interface)
    - [Make to Donation. Your Contribution Will Make to Difference.](#make-to-donation-your-contribution-will-make-to-difference)
    - [Find me on:](#find-me-on)
  - [Technologies used / used technologies](#technologies-used--used-technologies)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->


# Data-Auditor

[Readme version in English] (./ Readme-en.md)

## Optimize your database design with Data-Auditor!
Do you want to make sure that your database is free of redundancies, anomalies and design problems? With Data-Auditor, obtain the necessary tools to validate normal forms, analyze functional dependencies and guarantee a robust and efficient design. Try our command line interface and take your database to the next level!

## License

This Code is licensed under the general public license of GNU version 3.0 or posterior (LGPLV3+). You can find a complete copy of the license at https://www.gnu.org/licenses/lgPl-3.0-standalone.htmlalone.html0-standalone.html

## Characteristics What does Data-Auditor offer you?

Data-Auditor is an integral tool designed to evaluate and optimize the quality of your database designs. Offers a set of advanced functionalities that include:

Validation of normal ways: ensures that your design meets the first three normal forms, minimizing redundancy and avoiding anomalies in updates.

Verification of non -additive concatenation property: detects possible design problems that could affect the results of the consultations.

Analysis of functional dependencies: it facilitates the understanding of the relationships between the attributes of the tables, allowing a more robust and efficient design.

Command line interface: provides a simple and direct way to use the bookstore, ideal for integration in automated workflows.

With Data-Auditor, you can guarantee a solid database design, efficient and free of common errors.

## Challenges conquered / conquered challenges

- Formal demonstration of algorithms

## features to implement / characteristics to be implemented
- Detection of errors in the design of the database that affects the operation of the algorithms
- Validate that the names of tables and attributes entered as entry are valid
- Support for attributes, tables, FK and unconventional PK
- Support to recursive relationships

## Planning, Requirements Engineering and Risk Management / Planning, Requirements Engineering and Risk Management

These sections of the project will be carried out through a site in Notion so that they can be easily accessible by non -technical staff.

Request access link to authorized personnel

## Design Software / Software Design

### Structural perspective

#### Logica view of software architecture

In the following class diagram, key abstractions will be seen in the system, their responsibilities.


`` Mermaid
---
Title: Database Auditor
---
Class Diagram

class databaseauditor {

}

Note for database auditor "seeks to be the context for the different strategies used
and a medium that encapsulates utilitarian functions
common for all algorithms "

Databaseauditor ..> databaseschemagenerator

class database schema generator {
<< abstract>>
+Databaseauditor
+Generate ()
}

Note for database schema generator "provides the strategy interface
which is common to all concrete strategies
For the generation of
Database schemes "

Databaseschemagenerator ..> schemafromdbusingname
Databaseschemagenerator ..> Schemafromjson

Class Schemafromjson {
+Databaseauditor
+Generate ()
}

Class schemafromdbusingname {
+Databaseauditor
+Generate ()
}

Note for schemafromdbusingname "is one of the specific strategies
that generates the schemes by means of the names
of the name conventions of the database "

Databaseauditor ..> Validationalgorithm

Note for Validation Algorithm "provides the strategy interface
which is common to all concrete strategies
For the generation of
Validations of the database "

Class Validation Algorithm {
<< abstract>>
+Execute ()
}

Validationalgorithm <|- NonadditIVeconcatenation
Validationalgorithm <|- Verificationbcnf


Class Verification Nonadditive Concatenation {
+Databaseauditor
+Execute ()
}


Note for non additive concatenation "encapsulates algorithm 11.1 verification
of the non -additive concatenation property proposed by Ramez Elmasri
and Shamkant B. Navathe "

CLASS VERIFICATIONBCNF {
+Databaseauditor
+Execute ()
}

Note for non additive concatenation "encapsulates the algorithm that validates that each
Decomposition possesses the BCNF
Based on the definition presented
By Ramez Elmasri and Shamkant B. Navathe "

Databaseauditor <.. client

Class client {

}

``

### Behavior perspective

#### Schemafromdatabaseusingname.Generatejoinscls Process / SchemaphromDatabaseusingName.

`` Mermaid
---
Title: SchemafromDatabaseusingname process.
---
Statediagram-V2
Gettleables: Obtain the tables

[*] -> Gettables

Gettables -> Foreachtable

Note Left of Foreachtables: "This activity is executed for each table"
Foreachtable State {

State IF1 << Choice>>

[*] -> Relationable = gettenableytomanyrelationship (table)
Relationable = gettenableytomanyrelationship (table) -> if1
IF1 -> Manytomanyrelationshipanalysis: Relationables! = Null

State Manytomany Relationship Analysis {

State IF2 << Choice>>

[*] -> If2

IF2 -> [*]: Isrecursive (Table)

if2 -> addmanymanyable: else

Addmanymanyableable: Joinsclters [Last] [] = Manytomanyable

Addmanymanyyable -> Foreachrelationables

Note Left of Foreachrelationables: "This activity is executed by each table that participates in the relationship much"

FOREACHRELATIONABLE STATE {

ADDRELATIONABLE: JOINSCLUSTERS [Last] [] = RELATIONABLE

[*] -> Addrelationable

ADDRELATIONABLE -> [*]

}

Foreachrelationables -> [*]

}

MSAYTOMANYRELATIONSHIPANALYSIS -> Normal Relationship

IF1 -> Normal Relationshipshysis: Relationables == null

NormalrelationthiShipanalysis State {

[*] -> fks = getfkbyable (table)

FKS = Getfkbyable (Table) -> Excludeytomanyfk

State IF3 << Choice>>
Excludeytomanyfk -> If3

IF3 -> [*]: ELSE

Addreferencingable: Joinsclters [Last] [] = Referenceing
IF3 -> Addreferencingable: FKS! = Null

Addreferencingable -> Foreachfk
Note Left of Foreachfk: "This activity is executed for each table that a fk points to it"
State Foreachfk {

ADDREFERENCEDABLE: JOINSCLUSTERS [] = Referenced

[*] -> Addreferencedable

ADDREFERENCEDABLE -> [*]

}

Foreachfk -> [*]

}

Normal Relationshipshysis -> [*]

}


``

#### Schemafromdatabaseusingname.Generate () Process / SchemafromDatabaseusingname.Generate () process ()

`` Mermaid
---
Title: SchemaphromDatabaseusingname process.Generate ()
---
Statediagram-V2

[*] -> Gettables

STATE GETTABLE {

State IF2 << Choice>>

[*]-> If2

IF2-> Gettablesincludemode: Mode == Include
Gettablesincludemode-> [*]

IF2-> Gettables Excludemode: Mode == Exclude
Gettablesxcludemode-> [*]

}

Gettables -> Foreachtable

Note Left of Foreachtables: "This activity is executed for each table"

Foreachtable State {

[*] -> GetcolumnsFullyqualifiedform

Note Left of GetcolumnsFullyqualifiedForm: "If a column is not a columnname Foraneal Key = Pluraltosingular (Tablaname) .Columnname"

GetcolumnsFullyqualifiedform -> Foreachcolumn

Note Left of Foreachcolumn: "This activity is executed for each column"

State Foreachcolumn {

State IF3 << Choice>>

[*]-> If3
Addcolumniversal Relationship: "Add column to the universalrelationship"
AddcolumntableDecomposion: "Add column to the decomposition of the table"

IF 3 -> ADD UNIVERSAL COLUMN RELATIONSHIP:! Column in Universal Relationship
ADDCOLUMNIVERSALRELATIONSHIP-> ADDCOLUMINTABLEDECOMPOSITION

IF3 -> Addcolumntabledcomposion: ELSE
AddcolumntableDecomposion -> [*]

}

Foreachcolumn-> Addtrivial FunctionalDependence

State Addtrivial FunctionalDependence {
Addpkfunctionaldependency: If there is a PK add PK as a history of functional dependence and other attributes as consequent
[*]-> AddpkfunctionalDependNy
Addpkfunctionaldependency-> [*]
}

ADDTRIVIALFUNCTIONAL DEPENCY -> [*]

}
``

## Verification and Validation / Validation and Verification

### Formal Validation / Formal Validation

#### Getfunctional Dependenciesforbcnfintable

The objective of this section is to demonstrate that the algorithm can be used to generate the set of functional units necessary to validate the BCNF (normal form of Boyce-Codd).

The new algorithm that we will present is based on the idea that, to apply the verification of BCNF in each decomposition, we can use the set of functional dependencies in which both the antecedent and the consequent are subsets of the set of decomposition attributes set , instead of using the projection of the set of functional units for that decomposition, since this last option turns out to be much more demanding in algorithmic terms

For this it is important to keep in mind that

An R -relationship scheme is in BCNF if whenever a non -trivial functional dependence x → A is fulfilled in R, then x is a superclave of R.

##### BCNF Definition / BCNF definition

! [Another way to express the BCNF] (images/bcnf_definition.png)

and that the set of functional dependencies for a decomposition is the projection of the set of universal relationship dependencies for a decomposition.

That is to say

! [DEFINITION OF THE PROJECTION OF F ON RI] (images/definition_projection_f_ri.png)

Where f+ is the closure of a set of functional units

##### CLOSING A SET OF FUNCTIONAL DEPENDENCIES / CLASURA OF A SET OF FUNCTIONAL DEPENDENCES

Formally, the set of all the units that include F, together with the units that can be inferred from F, are called C closures; It is designated by f+.

##### Inference Rules for Functional Depending on Inference Rules for Functional Units

Recall that the well -known inference rules for functional units are
! [Inference Rules for Functional dependence] (images/inference_rules_functional_dependencies.png)

Having said this, note that the following happens for the inference rules when applied to the BCNF algorithm

###### Reflesive rule

In the case of the reflexive rule, the functional units generated x-> and require that X be subset of y so that it only generates trivial dependencies which are not taken into account in BCNF

###### Transitive rules, decomposition and union

In the case of transitive rules, decomposition and union the antecedent of the functional units does not change so that if the background is super key, the decomposition will fulfill the BCNF rule in case the decomposition is not super key. of BCNF, that is, the inferred decompositions with these rules will not affect the result

###### Increase and pseudo-transitivity rules

In the case of the Rules of Increase and Pseudo-Transitivity it happens that the antecedent X joins another W or Z set In any case it happens that:
If x is super key, only rules can be inferred in which the background remains super key
If x is not super key, rules that are super keys can be inferred but in case X is not super key we will already know that by the definition of BCNF this form is not met.

In conclusion:
In the case of BCNF validation we can use the


## Documentation

The Data-Auditor package allows a series of validations and improvements to the quality of the design of a database such as the check of normal forms, verification
of the non -additive concatenation property, etc.

### Conventions used during documentation

Notation conventions for grammar:

<> Are used to surround a non -terminal symbol

The :: = Used for production rules

Non -terminal symbols are expressed as a normal chain or characters

The following group of symbolic pairs, should be used together with expressions as follows: the first in each couple is written as suffix after the expression and the second surrounds the expression.

He ? or [] indicate that the expression is optional

The * or {} indicates that the expression is repeated or more times

The + indicates that the expression is repeated 1 or more times

If you want to use one of the previous characters, it must be preceded \ with

### Generation of schemes

For the algorithms to work, a representation of the database must be generated
on which to apply these algorithms

Unfortunately some SGBD support SQL standards differently so there is no universal algorithm that works perfectly for all SGBD.

So that the software is designated taking into account the possibility of using different algorithms. More specifically the Strategy pattern will be used to allow the exchange of algorithms

The responsible structures will be the following:

#### Databaseauditor
It seeks to be the context for the different strategies used and a medium that encapsulates common utilitarian functions for all algorithms

#### Database Schema Generator
Provides the strategy interface
which is common to all concrete strategies
For the generation of
Database schemes

#### Schemafromdbusingname
It is one of the concrete strategies that generates the schemes through names
of the columns of the database

Although the set of information_schema views could be used to determine some of the database structures

It was discovered that Joins Additives were usually generated in PostgreSQL

For example, if you would like to know if a column in the information_schema.Key_column_usage is a primary key, foreign key, etc., the query should be used

`` sql
Select Kcc.Column_name, Kcc.
From
Information_schema.able_constraints TC
Join
INFORMATION_SCHEMA.KEY_COLUMN_USAGE KC
On Tc.constraint_name = kcu.constraint_name
``
However, if we carefully observe these results, they are affected by an additive Join

For the particular case of having
`` sql
Select column_name, table_name from information_schema.Key_column_usage where table_name ~ '^insight_taxonomy $'
``

that shows results in this way

! [Information_schema.Key_column_usage_where_name_insight_taxonomy] (images/key_column_usage_where_nable_name_insight_taxonomy.png)

and a
`` sql
Select TC.Constraint_name
From
Information_schema.able_constraints TC
WHERE
tc.constraint_name ~ '^taxonomy_id_fkey $'
``
that shows results in this way

! [Table_constraints_where_taxonomy_id_fkey] (images/table_constraints_where_taxonomy_id_fkey.png)

You have to

`` sql
Select Kcc.Column_name, Kcc.
From
Information_schema.able_constraints TC
Join
INFORMATION_SCHEMA.KEY_COLUMN_USAGE KC
On Tc.constraint_name = kcu.constraint_name
WHERE
Kcc.able_name ~ '^insight_taxonomy $'

``

generates the following results that reflect the join additive

! [Adivivoin.png] (images/adionntivejoin.png)

##### Name conventions used for the identification of elements

###### Primary keys
All ID name attribute

###### Examples
id

###### Foraneal keys
Any attribute that has the following way
``
<Tabllasigular name> [_ <bol>] _ id

Where [role] serves to identify the entity in recursive relationships

``

Coincides with the following regular expression
``
^[a-za-z0-9ñ]+(?: _ [a-za-z0-9ñ]+)? _ Id $
``
###### Examples
- User_id
- Taxonomy_child_id
- Taxonomy_Parent_id

### Validation of database schemes

The objective of this library is to provide validations for the database schemes and for this the following structures will be used:

#### Validation Algorithm
Provides the strategy interface that is common to all specific strategies for the generation of the database validations

##### Verification

Encapsulates algorithm 11.1 Verification of the non -additive concatenation property proposed by Ramez Elmasri and Shamkant B. Navathe

##### VerificationBCNF

Encapsulates the algorithm that validates that each decomposition possesses the BCNF based on the definition presented by Ramez Elmasri and Shamkant B. Navathe

For the algorithm, the set of non -trivial functional units will be used in which both the antecedent and the consequent are subset of the set of attributes of the decomposition, instead of using the set of non -trivial units in the projection of the set of functional dependencies for This decomposition this because for the end of the algorithm to verify the BCNF, the sets work equivalently.

The formal demonstration of this statement is found in the readmad of the Database-Auditor package.

### Use

#### Requirements

##### Facility

###### as a user

Composer Install Israeldavidvm/Database-Auditor

Global Composer Require Israeldavidvm/Database-Auditor

Composer requires Israeldavidvm/Database-Auditor


###### as a library (only if you want to create a program that uses library)
Composer requires Israeldavidvm/Database-Auditor

##### File .env (this is necessary when you want to generate a scheme from the database the default behavior)

Establish a configuration in the .env file. Like the next

``

DB_connection = PGSQL
Db_host = 127.0.0.1
DB_Port = 5432
Db_database = <databasename>
Db_usename = <suername>
DB_Password = <password>

``

#### use from the command line interface

In order to use the program you will only need an .EV file with the configuration of your database as specified above and execute the command

** If it is included in a project through require with the global (composer global requires Israeldavidvm/Database-Auditor) **

`` ~/.config/composer/vendor/bin/database-auditor app: audit-database [<validationalgorithms> [<databaseschemageneratoraconfig>]] `` `` `` `` `

** If it is included in a project through require without the global (composer requires Israeldavidvm/Database-Auditor) **

``.

** If it is installed by installing or starts from the project root (composer install Israeldavidvm/Database-Auditor) **

`` Composer Audit-Database [<Validationalgorithms> [<databaseschemageneratoryconfig>]] `` ``

Description:
This command allows you


Arguments:
Validationalgorithms Value of the types of validation algorithm to apply separated by coma (,) Exam
Databaseschemageneratoraconfig Cadena that specifies the databaseschemagenerator and its configuration of the chain has a format like the following <databaseschemagenerator> | <Config> where <databaseschemagenerator> is the value of the type of database generator From the database scheme generator that will depend on the type for the case of Schemafromdatabaseusingneme it has the <mode> | <table> | <Path> [default: "SchemafromDatabaseusingname format | Exclude | Users, Migrations, password_resets, failed_jobs, personal_access_tokens, Taxonomy_taxon and | ./. Sub "]

Options:
-H, -Help Display Help for the Given Command. WHEN NO COMMAND IS GIVEN DISPLAY HELP FOR THE LIST COMMAND
-Silent do not output any message
-q, --quie Only Error Displayed. All other output is support
-V, -Version Display This Application Version
`` Ansi | --No-Ansi Force (OR DISABLE --No-Ansi) ANSI OUTPUT
-N, --No-Interaction do not ask any interactive question
-V | VV | VVV, -Verbose Increase The Verbosity of Messages: 1 For Normal Output, 2 For More Verbose Output and 3 for Debug


### Make to Donation. Your Contribution Will Make to Difference.
[! [ko-fi] (https://ko-fi.com/img/githubutton_sm.svg)] (https://ko-fi.com/israeldavidvm)
[! [PayPal] (https://img.shields.io/badge/paypal-@israeldavidvm-0077b5?style=for-the-badge&ogo=paypal&logocolor=white&labelColor=101010)] (https://paypal.me/israeldavidvm )
[! [Binance] (https://img.shields.io/badge/binance_id-809179020-1010 ?style=for Activity/Referral-Entry/CPa? Ref = CPA_004ZGH9EIS)

### Find me on:
[! [Github] (https://img.shields.io/badge/github-israeldavidvm-gray?
[! [LinkedIn] (https://img.shields.io/badge/linkedIn-israeldavidvm-0077b5?style=for-the-badge&ogo=LinkedI in/Israeldavidvm/)
[! [Twitter] (https://img.shields.io/badge/twitter-@israeldavidvm-1da1f2?style=FOR-the-badge&ogo=twitter&logocolor=white&labelColor=101010)] (https://twitter.com/israeldavidvm )
[! [Facebook] (https://img.shields.io/badge/facebook-israeldavidvm-1877f2? Israeldavidvm)
[! [Instagram] (https://img.shields.io/badge/instagram-@israeldavidvmv-gray?style=for-the-badge&ogo=instagram&logocolor=White&labelColor=101010)] (https://www.instagram.com /Israeldavidvm/)
[! [Tiktok] (https://img.shields.io/badge/tiktok-@israeldavidvm-e4405f?style=FOR-the-badge&ogo=tiktok&logocolor=white&labelColor=101010)] (https://www.tiktok.com /@Israeldavidvm)
[! [YouTube] (https://img.shields.io/badge/youtube-@israeldavidvm-ff0000?style=FOR-the-badge&ogo=youtube&logocolor=white&labelcolor=101010)] (https://www.youtube.com /Channel/UCMZLFPENPDWPJOHAL0WRY7A)

## Technologies used / used technologies

[! [PHP] (https://img.shields.io/badge/php-blue?ogo=php&style=for
