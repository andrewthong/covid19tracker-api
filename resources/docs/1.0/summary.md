# Summary

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [By Province](#province)

> {warning} This route is under consideration for deprecation.

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
  "total_cases": 2337,
  "total_fatalities": 103
}
```

## By Province

Appending a province code will filter the totals to that province.

| Method | URI |
| :- | :- |
| GET | `/summary/province/:code` |

### Example

`summary/province/bc`