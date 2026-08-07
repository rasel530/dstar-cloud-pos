$f = "E:\POS Software\D Star Company\resources\views\customers\index.blade.php"
$c = [System.IO.File]::ReadAllText($f)

# Show current toggle buttons
$matches = [regex]::Matches($c, '@click[^"]*toggleStatus[^"]*')
Write-Output "ToggleStatus buttons: $($matches.Count)"

# Count current divs for reference
$opens = ([regex]::Matches($c, '<div[ >]')).Count
$closes = ([regex]::Matches($c, '</div>')).Count
Write-Output "Original divs: opens=$opens closes=$closes"
