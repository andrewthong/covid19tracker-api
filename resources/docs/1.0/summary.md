# Summary

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [By Province](#province)

<a name="basic"></a>
## Basic Usage

Returns a summary of total cases and fatalities.

| Method | URI |
| :- | :- |
| GET | `/summary` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "data": [
    {
      "latest_date": "2020-04-13",
      "change_cases": null,
      "change_fatalities": null,
      "change_tests": "6464",
      "change_hospitalizations": "23",
      "change_criticals": "2",
      "change_recoveries": "240",
      "total_cases": "18554",
      "total_fatalities": "417",
      "total_tests": "433650",
      "total_hospitalizations": "1801",
      "total_criticals": "571",
      "total_recoveries": "7412"
    }
  ],
  "last_updated":"2020-04-26 12:40:18"
}
```

Last updated refers to the last time the reports were processed, in America/Regina time.

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
      "province": "ON",
      "date": "2020-04-13",
      "change_cases": null,
      "change_fatalities": null,
      "change_tests": 5150,
      "change_hospitalizations": 22,
      "change_criticals": 2,
      "change_recoveries": 236,
      "total_cases": 5396,
      "total_fatalities": 189,
      "total_tests": 106696,
      "total_hospitalizations": 760,
      "total_criticals": 263,
      "total_recoveries": 3357
    },
    ...
  ]
}
```