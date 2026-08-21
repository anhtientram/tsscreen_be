# Cổng 4 — Dev

Chỉ sau cổng 1–3.

## Làm

- Route `routes/legacy.php`, group middleware `api`.
- Controller `app/Http/Controllers/Legacy/`, `LegacyJson::send`.
- `forceJson: true` khi app không `jsonDecode` (hiện: `Get_Transactions_ByCustomerId`, `getQRCode_ByPaidId`).
- OA attributes + `App\OpenApi\AppTags` (Customer / Projector / Admin; dùng chung gắn đủ).
- Model serialize: `LegacyJson::str` cho ID.
- Password: customer plaintext vào bcrypt; admin MD5 hex rồi bcrypt. JSON không trả hash.
- Skill [legacy-api](../skills/legacy-api/SKILL.md).

Không sửa 3 app Flutter trừ Phase 7 (tắt poll) khi user yêu cầu.
