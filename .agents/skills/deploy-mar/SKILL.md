---
name: deploy-mar
description: >-
  Se activa y debe usarse cuando el usuario escribe el texto "quiero deployar" (o similares). Ejecuta la secuencia de pasos de preparación y despliegue del sistema MAR.
---

# Script de Despliegue (Deploy MAR)

Cuando el usuario indique "quiero deployar", DEBES ejecutar la siguiente secuencia de forma secuencial. Si alguna operación no se ejecuta correctamente, cancela la ejecución restante y explícale al usuario qué falló.

## Secuencia de Operaciones

### 1. Actualizar fechas de la etiqueta de versión
Cambia la fecha y hora de la etiqueta de versión en el sistema a la fecha actual. 
El formato objetivo debe ser: `"Versión X.X · DD Mes AAAA · HH:MM hrs"` (ejemplo: "Versión 1.2 · 08 oct 2026 · 12:13 hrs"). 
Asegúrate de conservar el número de versión (X.X) actual.
Debes actualizar dos archivos:
- `C:\Cota\MAR\frontend\src\pages\auth\Login.jsx`
- `C:\Cota\MAR\frontend\src\layouts\dashboard\navbar\NavbarVertical.jsx`

### 2. Revisión de código (Lint) y Build
Ve a la carpeta del frontend (`C:\Cota\MAR\frontend`) y ejecuta mediante la terminal:
- `npm run lint` (Si falla, no continúes. Si pasa, sigue al build).
- `npm run build`
Espera a que los procesos terminen. Si alguno arroja error 500, falla o tiene errores de sintaxis, **cancela** la ejecución y explica al usuario.

### 3. Git Add, Commit, y Push
Ve a la raíz del repositorio (`C:\Cota\MAR`) y realiza los siguientes comandos:
- `git status` y `git diff` (para identificar los cambios).
- `git add .`
- Formula un mensaje de commit que resuma adecuadamente los cambios realizados. **Importante:** Como eres el agente, genera y ejecuta el comando `git commit -m "..."` directamente usando tu mejor sugerencia.
- `git push`
*(Nota: Si hay algún conflicto o el push requiere intervención, detente y avisa al usuario).*

### 4. Resumen final
Avisa al usuario explícitamente qué operaciones se realizaron, muestra el mensaje de commit que utilizaste e infórmale que el proceso de despliegue ha concluido con éxito.
