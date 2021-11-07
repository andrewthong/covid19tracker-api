# Summary

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [By Province](#province)

<a name="basic"></a>
## Basic Usage

Returns a summary of data based on the latest available information. This is the latest reported date in the dataset across all provinces.

| Method | URI |
| :- | :- |
| GET | `/summary` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "data":[
    {
      "latest_date":"2021-02-21",
      "change_cases":"2301",
      "change_fatalities":"41",
      "change_tests":"88097",
      "change_hospitalizations":"-61",
      "change_criticals":"12",
      "change_recoveries":"2707",
      "change_vaccinations":"40424",
      "change_vaccinated":"11039",
      "change_boosters_1":"2000",
      "change_vaccines_distributed":"0",
      "total_cases":"850482",
      "total_fatalities":"21673",
      "total_tests":"24094600",
      "total_hospitalizations":"2274",
      "total_criticals":"555",
      "total_recoveries":"797494",
      "total_vaccinations":"1492270",
      "total_vaccinated":"415505",
      "total_boosters_1":"2000",
      "total_vaccines_distributed":"1851710"
    }
  ],
  "last_updated":"2021-02-21 17:38:06"
}
```

### data
An array of summary. When reporting on all provinces, there will only be one object in this array.

### last_updated
Refers to the last time the reports were processed, in America/Regina time.

<a name="split"></a>
## Split

This will not aggregate all province totals.

| Method | URI |
| :- | :- |
| GET | `/summary/split` |

### Sample Response

```json
{
  "data": [
    {
      "province":"ON",
      "date":"2021-02-21",
      "change_cases":1031,
      "change_fatalities":10,
      "change_tests":48178,
      "change_hospitalizations":-39,
      "change_criticals":14,
      "change_recoveries":1081,
      "change_vaccinations":16404,
      "change_vaccinated":6771,
      "change_boosters_1":2000,
      "change_vaccines_distributed":0,
      "total_cases":297924,
      "total_fatalities":6859,
      "total_tests":10581076,
      "total_hospitalizations":660,
      "total_criticals":277,
      "total_recoveries":280754,
      "total_vaccinations":556533,
      "total_vaccinated":235922,
      "total_boosters_1":2000,
      "total_vaccines_distributed":683255
    },
    ...
  ]
}
```

<a name="split-by-health-region"></a>
## Split by Health Region

This is similar to split but provides current data for each individual health region.

Note that while most vaccination data is available by health region,  vaccines *distributed* to each health region is not available. As well, some provinces do not report any vaccination data by health region.

| Method | URI |
| :- | :- |
| GET | `/summary/split/hr` |

### Sample Response

```json
{
  "data": [
    {
      "hr_uid":3526,
      "date":"2021-03-15",
      "change_cases":3,
      "change_fatalities":0,
      "change_tests":1456,
      "change_hospitalizations":1,
      "change_criticals":null,
      "change_recoveries":1,
      "change_vaccinations":2364,
      "change_vaccinated":-2,
      "change_boosters_1":3,
      "total_cases":208,
      "total_fatalities":4,
      "total_tests":106226,
      "total_hospitalizations":2,
      "total_criticals":0,
      "total_recoveries":198,
      "total_vaccinations":8784,
      "total_vaccinated":1060
      "total_boosters_1":43,
    },
    ...
  ]
}
```
