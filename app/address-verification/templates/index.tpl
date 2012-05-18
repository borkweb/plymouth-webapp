{box title="Address Verification Forms"}

{if $smarty.session.AUTHZ.permission.address_verification_spraddr}
Run address verification against <a href="{$PHP.BASE_URL}/spraddr">SPRADDR</a><br/>
{/if}
{/box}
