-- Script para arreglar horarios sin start_date
-- Actualiza los horarios existentes para que usen su fecha de creación como start_date

UPDATE work_schedules 
SET start_date = DATE(created_at)
WHERE start_date IS NULL;

-- Verificar cuántos se actualizaron
SELECT 
    COUNT(*) as total_schedules,
    SUM(CASE WHEN start_date IS NOT NULL THEN 1 ELSE 0 END) as with_start_date,
    SUM(CASE WHEN start_date IS NULL THEN 1 ELSE 0 END) as without_start_date
FROM work_schedules;
