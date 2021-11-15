# Vaccination Data

---

- [Overview](#basic)
- [Health Region](#hr)
- [Provincial](#provincial)
- [National](#national)

<a name="basic"></a>

## Overview

Vaccination data has been of particular interest over recent months. This section aims to assist in the navigation of our API to enable quick, easy access to vaccination data at the health region, provincial and national levels.

The attributes for vaccination data are the same across endpoints, with the exception of subregions which is outlined in the next section.

| Attribute | Description|
| :- | :- |
|`vaccinations`| The total number of doses administered| 
|`vaccinated`| The total number of people fully vaccinated; effectively, the total number of 2nd doses administered.| 
|`boosters_1`| The total number of additional (3rd) doses administered.| 
|`vaccines_distributed`| The total number of vaccines delivered to a province for administration.| 



<a name="hr"></a>
### Subregion Data
Vaccination data by subregion is available for select provinces and territories.

| Type | Dates | URI | Summary |
| :- | :- |:- |:- |
| Single Subregion | All| `/reports/sub-regions/CODE` |Returns time-series of all data available for a subregion |
| All Subregions | Current | `/reports/sub-regions/summary` |Returns current data for all subregions|
| All Subregions | Recent | `/reports/sub-regions/recent` |Returns the 15 most recent reports for each subregion|
| All Subregions | All |Due to large payload, only available upon request. |Returns time-series of all data for all subregions|

As previously mentioned, the attributes for subregion level vaccination data are different than all other vaccination endpoints. The attributes are as below:
| Attribute | Description|
| :- | :- |
|`total_dose_x`| The total number of a particular dose administered in the region.| 
|`percent_dose_x`| The percentage of residents in each subregion who have received a particular dose.| 
|`source_dose_x`| The format of the original source data; `total` or `percent`| 

Some jurisdictions only report percentages or only raw data, and not both. However, when precise population data is available, the source data (either total or percent) is automatically converted to an estimated percentage or estimated total value, making both formats available to users. The `source_dose_x` attribute indicates which format is the original, precise value. 

This permits use of a consistent format across subregions with differing sources.

#### Eligible and Total Percentages
To further add to complexity, total population data is not available for all sub-regions. Instead, some have only released 'eligible' population values, which excludes the portion of the population that cannot yet receive the vaccine. Please refer to the table below to ensure that you are familiar with the significance of the data from subregions from each province: 
| Province | Subregion Percentage Type|
| :- | :- |
|AB| Total Population| 
|SK| Total Population| 
|NT| Total Population| 
|ON| Total Population| 
|NL| ***Eligible Population Only***| 



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
