<template>
  <div class="view">
    <store-header></store-header>
    <div class="cat-body">
      <div class="cat-sidebar">
        <div class="cat-inner-sidebar">
          <div class="cat-group">
            <!-- Las categorías se cargan desde Laravel y filtran el catálogo. -->
            <a class="cat-group-title" @click="seleccionarCategoria(null)">TODAS LAS CATEGORÍAS</a>
            <div class="cat-sub">
              <a
                v-for="categoria in categorias"
                :key="categoria.id"
                :class="{activa: categoriaSeleccionada === categoria.id}"
                @click="seleccionarCategoria(categoria.id)"
              >{{ categoria.nombre }}</a>
            </div>
          </div>
          <div class="filter-box">
            <h4>Filtrar por precio</h4>
            <input type="range" min="0" :max="precioMaximo" v-model.number="filtroPrecio">
            <div class="price-label">Precio: 0$ – {{ filtroPrecio }}$</div>
          </div>
        </div>
      </div>
      <div class="cat-main">
        <div class="cat-toolbar">
          <h3>Ordenar por:</h3>
          <select v-model="ordenarPor">
            <option value="menor">Menor Precio</option>
            <option value="mayor">Mayor Precio</option>
            <option value="vendidos">Más vendidos</option>
          </select>
        </div>
        <p v-if="cargando" class="producto-vacio">Cargando productos...</p>
        <p v-else-if="error" class="producto-error">{{ error }}</p>
        <div v-else class="cat-grid">
          <cat-card v-for="producto in itemsGrilla" :key="producto.uid" :product="producto"></cat-card>
        </div>
        <p v-if="!cargando && !error && !itemsGrilla.length" class="producto-vacio">No hay productos para estos filtros.</p>
      </div>
    </div>
  </div>
</template>

<script>
import StoreHeader from '../components/StoreHeader.vue';
import CatCard from '../components/CatCard.vue';
import api from '../Api/api.js';

export default {
  name: 'CategoriaView',
  components: { StoreHeader, CatCard },
  data() {
    return {
      filtroPrecio: 4000,
      ordenarPor: 'menor',
      // Datos reales del catálogo público.
      productos: [],
      categorias: [],
      categoriaSeleccionada: null,
      cargando: false,
      error: ''
    };
  },
  computed: {
    precioMaximo() {
      const precios = this.productos.map(producto => Number(producto.precio_venta) || 0);
      return Math.max(4000, ...precios);
    },
    itemsGrilla() {
      return this.productos
        .filter(producto => {
          const pertenece = !this.categoriaSeleccionada || producto.categorias?.some(
            categoria => categoria.id === this.categoriaSeleccionada
          );
          return pertenece && Number(producto.precio_venta) <= this.filtroPrecio;
        })
        .sort((a, b) => {
          const precioA = Number(a.precio_venta) || 0;
          const precioB = Number(b.precio_venta) || 0;
          return this.ordenarPor === 'mayor' ? precioB - precioA : precioA - precioB;
        })
        .map(producto => ({
          ...producto,
          uid: producto.id,
          name: producto.nombre,
          price: Number(producto.precio_venta) || 0
        }));
    }
  },
  async mounted() {
    this.cargando = true;
    try {
      // Carga productos con sus categorías para evitar datos de prueba.
      const [productos, categorias] = await Promise.all([
        api.get('/productos'),
        api.get('/categorias')
      ]);
      this.productos = productos.data;
      this.categorias = categorias.data;
      this.filtroPrecio = this.precioMaximo;
    } catch (requestError) {
      console.error('Error al cargar el catálogo:', requestError);
      this.error = 'No se pudieron cargar los productos.';
    } finally {
      this.cargando = false;
    }
  },
  methods: {
    seleccionarCategoria(id) {
      this.categoriaSeleccionada = id;
    }
  }
};
</script>
