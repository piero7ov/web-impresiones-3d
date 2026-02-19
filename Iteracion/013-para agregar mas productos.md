## Idea general para el futuro

La regla mental es:

1. **Cuenta la posición** del producto: 1, 2, 3, 4… (ese es el `nth-child(n)`).
2. Decide qué ancho quieres para ese grupo:

    - 100% → fila completa → `flex-basis: 100%;`
    - 50% → 2 por fila → `flex-basis: calc(50% - 20px);`
    - 33.333% → 3 por fila → `flex-basis: calc(33.333% - 20px);`
    - 25% → 4 por fila → `flex-basis: calc(25% - 20px);`

3. Crea reglas tipo:

    ```css
    main article:nth-child(10),
    main article:nth-child(11),
    main article:nth-child(12) {
        flex-basis: calc(33.333% - 20px);
    }
    ```

Y listo, tú vas “pintando” el layout que quieras solo cambiando qué índices agrupa cada regla.

