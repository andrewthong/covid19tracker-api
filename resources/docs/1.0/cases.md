# Cases

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Parameters](#parameters)
- [Single Record](#single)

<a name="basic"></a>
## Basic Usage

Returns the latest 100 cases.

| Method | URI |
| :- | :- |
| GET | `/cases` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 10000,
      "province": "ON",
      "city": "Example City",
      "age": "60",
      "travel_history": "Pending",
      "confirmed_presumptive": "CONFIRMED",
      "source": "https://www.example.net",
      "date": "2020-03-30 19:45:00"
    },
    ...
  ],
  "first_page_url": ".../cases",
  "from": 1,
  "last_page": 100,
  "last_page_url": ".../cases?page=100",
  "next_page_url": ".../cases?page=2",
  "path": ".../cases",
  "per_page": "100",
  "prev_page_url": null,
  "to": 100,
  "total": 10000
}
```

<a name="parameters"></a>
## Parameters

| Parameter | Type | Description |
| :- | :- | :- |
| province | String | Filter cases to a specific province e.g. `QC` |
| per_page | Integer | Number of cases per page (max 1000) |
| order | `DESC`\|`ASC` | If `ASC`, oldest cases are shown first. Defaults to `DESC` |

### Example
`/cases?province=ON&per_page=50`

<a name="single"></a>

## Single Record

Returns a single case record by ID.

| Method | URI |
| :- | :- |
| GET | `/case/:id` |

### Example

`/case/123`