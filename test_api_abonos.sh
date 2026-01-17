#!/bin/bash

# Script de prueba para API de Abonos Móvil
# Colores para output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

BASE_URL="http://127.0.0.1:8002/api/v1/movil"

echo "========================================="
echo "PRUEBAS DE API DE ABONOS MÓVIL"
echo "========================================="
echo ""

# Test 1: Listar todos los apartados
echo -e "${YELLOW}Test 1: Listar todos los apartados${NC}"
echo "GET $BASE_URL/apartados"
echo "---"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/apartados" -H "Accept: application/json")
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)
BODY=$(echo "$RESPONSE" | head -n -1)

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY" | head -20
elif [ "$HTTP_CODE" -eq 404 ]; then
    echo -e "${YELLOW}⚠ Status: $HTTP_CODE - No hay apartados${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
    echo "$BODY" | head -20
fi
echo ""
echo "========================================="
echo ""

# Test 2: Listar apartados solo activos
echo -e "${YELLOW}Test 2: Listar apartados activos${NC}"
echo "GET $BASE_URL/apartados?estado=activo"
echo "---"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/apartados?estado=activo" -H "Accept: application/json")
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)
BODY=$(echo "$RESPONSE" | head -n -1)

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY" | head -20
elif [ "$HTTP_CODE" -eq 404 ]; then
    echo -e "${YELLOW}⚠ Status: $HTTP_CODE - No hay apartados activos${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
    echo "$BODY" | head -20
fi
echo ""
echo "========================================="
echo ""

# Test 3: Buscar por folio (ejemplo)
echo -e "${YELLOW}Test 3: Buscar por folio${NC}"
echo "GET $BASE_URL/apartados/buscar-folio/APT-2025-001"
echo "---"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/apartados/buscar-folio/APT-2025-001" -H "Accept: application/json")
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)
BODY=$(echo "$RESPONSE" | head -n -1)

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
elif [ "$HTTP_CODE" -eq 404 ]; then
    echo -e "${YELLOW}⚠ Status: $HTTP_CODE - Apartado no encontrado${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
    echo "$BODY" | head -20
fi
echo ""
echo "========================================="
echo ""

# Test 4: Buscar por cliente
echo -e "${YELLOW}Test 4: Buscar por cliente${NC}"
echo "GET $BASE_URL/apartados/buscar-cliente?nombre=Juan"
echo "---"
RESPONSE=$(curl -s -w "\n%{http_code}" "$BASE_URL/apartados/buscar-cliente?nombre=Juan" -H "Accept: application/json")
HTTP_CODE=$(echo "$RESPONSE" | tail -n 1)
BODY=$(echo "$RESPONSE" | head -n -1)

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✓ Status: $HTTP_CODE${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
elif [ "$HTTP_CODE" -eq 404 ]; then
    echo -e "${YELLOW}⚠ Status: $HTTP_CODE - Cliente no encontrado${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
elif [ "$HTTP_CODE" -eq 400 ]; then
    echo -e "${YELLOW}⚠ Status: $HTTP_CODE - Parámetro faltante${NC}"
    echo "$BODY" | jq '.' 2>/dev/null || echo "$BODY"
else
    echo -e "${RED}✗ Status: $HTTP_CODE${NC}"
    echo "$BODY" | head -20
fi
echo ""
echo "========================================="
echo ""

echo -e "${GREEN}Pruebas completadas!${NC}"
echo ""
echo "NOTA: Los tests de POST (registrar abono) requieren datos existentes"
echo "      y se deben hacer manualmente con IDs válidos."
