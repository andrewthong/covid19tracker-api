# Health Regions

---

- [Basic Usage](#basic)
- [Sample Response](#sample-response)
- [Specific Region](#single)
- [By Province](#by-province)

<a name="basic"></a>
## Basic Usage

Returns a list of regions, including the hr_uid, which becomes helpful when seeking data for specific health regions with other endpoints.

| Method | URI |
| :- | :- |
| GET | `/regions` |

<a name="sample-response"></a>
## Sample Response

```json
{
  "data": [
    {
      "hr_uid":471,
      "province":"SK",
      "engname":"Far North",
      "frename":"Far North"
    },
  ...
  ]
}
```

<a name="single"></a>
## Get a specific Region

Returns a single health region record by Health Region UID (hr_uid)

| Method | URI |
| :- | :- |
| GET | `/regions/:hr_uid` |

### Example

`/regions/3553`

<a name="by-province"></a>
## Regions by Province

Returns all Health Regions assigned to a Province.

| Method | URI |
| :- | :- |
| GET | `/province/:code/regions` |

### Example

`/province/SK/regions`
