let script = `
<?php

if (PHP_SAPI !== 'cli') {
  exit('Command line only execution allowed.');
}

echo "Good";
`;

export { script };