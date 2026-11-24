# R7 production reconciliation — 2026-11-25
**DRAFT / DO NOT MERGE OR DEPLOY.** This branch is a partial reconstruction from the deployment patches, not an exact production source export.

Production verification reported on 2026-11-24 UTC:
- R7 frontend release performance-r7-20261124T232308Z; backend size-guide-image-AuggafOs. R6 retained for rollback.
- 30/30 HTTP checks passed, 92/92 original media files present/readable, 260 responsive variants for 68 product images; mean 640px card size 464.8 → 73.3 KB (84.2%).
- Image optimizer ran successfully and its enabled timer showed next execution in five minutes. HTTP/2, 24-hour image cache, hashed asset cache, Laravel route/config caches and request-scoped SSR bootstrap deduplication verified.
- Three backup archives verified by SHA-256 on server and independently after downloading to owner's computer. Restore drill not yet performed.
- Source export filename lbb-source-r7.tar.gz; SHA-256 b5beacc46c17d68e0c93435b89b5e675f1f370ab6dcfd08f48073f6795af2b8e. Export has not been supplied to this reconciliation session. Do not upload database or recovery archives to GitHub.

Next: inspect sanitized source export; compare against both branches, reconcile every actual source change (including R2–R7 and prior production patches), record operational configuration without secrets, run full tests/CI and confirm parity before merging. Test checkout discount tiers 10/20/30%, free shipping, mobile/desktop browser performance and image uploads. Verify restore and preserve R7/R6 rollback dependencies before permanent deletion. Production is unchanged by this branch.

This branch currently recovers the R7 API transformer and optimizer PHP/systemd units from the installer; other production changes and deployment integration still need the exact export.
