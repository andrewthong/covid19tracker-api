# Vaccination Data

---

- [Overview](#basic)
- [Health Region](#hr)
- [Provincial](#provincial)
- [National](#national)

<a name="basic"></a>

## Overview

Vaccination data has been of particular interest over recent months. This section aims to assist in the navigation of our API to enable quick, easy access to vaccination data at the health region, provincial and national levels.

The attributes for vaccination data are the same across endpoints.

| Attribute | Description|
| :- | :- |
|`vaccinations`| The total number of doses administered| 
|`vaccinated`| The total number of people fully vaccinated; effectively, the total number of 2nd doses administered.| 
|`boosters_1`| The total number of additional (3rd) doses administered.| 
|`vaccines_distributed`| The total number of vaccines delivered to a province for administration.| 

<a name="hr"></a>
### Health Region Data
Vaccination data is available for most health regions.

| Type | Dates | URI | Summary |
| :- | :- |:- |:- |
| Single Health Region | All| `/reports/regions/HR_UID` |Returns time-series of all data available for a region, including vaccination data |
| All Health Regions | Current | `/summary/split/hr` |Returns current data for all regions, including vaccination data |
| All Health Regions | All |Due to large payload, only available upon request. |Returns time-series of data for all regions, including vaccination data |

<a name="provincial"></a>
### Provincial Data
Vaccination data is available for all provinces

| Type | Dates | URI | Summary |
| :- | :- |:- |:- |
| Single Province | All| `/reports/province/:code` |Returns time-series of all data available for a province, including vaccination data |
| All Provinces | Current | `/summary/split` |Returns current data for all provinces, including vaccination data |
| Single Provinces | All |`/vaccines/age-groups/province/:code` |Returns time-series of age data for vaccinations in a province |

<a name="national"></a>
### National Data

| Type | Dates | URI | Summary |
| :- | :- |:- |:- |
| National | All| `/reports/` |Returns time-series of all data available, including vaccination data |
| National | Current | `/summary/` |Returns current data, including vaccination data |
| National | All |`/vaccines/age-groups/` |Returns time-series of age data for vaccinations |





