<template>
  <div class="view">
    <store-header></store-header>
    <div class="product-detail">
      <div class="pd-image img-placeholder">
        <span v-if="productoCargado.icono" class="product-icono product-icono-grande" aria-hidden="true">{{ productoCargado.icono }}</span>
      </div>
      <div class="pd-info">
        <h1>{{ productoCargado.name }}</h1>
        <h4 style="color:var(--azul); font-size:24px; font-weight:800; margin:4px 0 14px;">{{ formatearPrecio(productoCargado.price) }}</h4>
        <h4>Especificaciones</h4>
        <div class="spec-table"><div class="c1"></div><div class="c2"></div></div>
        <button class="wishlist" :class="{active: favorito}" @click="favorito=!favorito">
          <span class="heart">♥</span> Añadir a deseados
        </button>
        <br>
        <button class="btn-primary" @click="agregar">Añadir al carrito</button>
      </div>
    </div>
    <div class="pd-desc">
      <h4>Descripcion:</h4>
      <p>{{ productoCargado.descripcion || 'Sin descripción disponible.' }}</p>
      <div class="pd-related">
        <h4>Talvez te interese...</h4>
        <div class="products-wrap" style="margin:14px 0;">
          <div class="product-row" style="border-bottom:none;">
            <product-card v-for="p in productosRelacionados" :key="p.id" :product="p"></product-card>
          </div>
        </div>
      </div>
    </div>
    <footer class="site-footer">DISCLAIMERS, CONTACTO, UBICACIÓN, ETC.</footer>
    <div class="toast" :class="{show: mostrarAviso}">Añadido al carrito ✓</div>
  </div>
</template>

<script>
import StoreHeader from '../components/StoreHeader.vue';
import ProductCard from '../components/ProductCard.vue';
import api from '../Api/api.js';
import { CATALOGO, formatearPrecio, obtenerProducto } from '../catalog.js';
import { useCarritoStore } from '../stores/carrito.js';

export default {
  name: 'ProductoView',
  components: { StoreHeader, ProductCard },
  // "id" llega solo como prop porque en el router pusimos props:true
  // en la ruta /producto/:id — así este componente no depende de
  // leer $route a mano.
  props: ['id'],
  data() {
    return {
      carrito: useCarritoStore(),
      favorito: false,
      mostrarAviso: false,
      producto: null,
      productosRelacionados: []
    };
  },
  computed: {
    productoCargado() { return this.producto || { name: '', price: 0, descripcion: '' }; }
  },
  methods: {
    formatearPrecio,
    agregar() {
      this.carrito.agregar(this.id);
      this.mostrarAviso = true;
      setTimeout(() => { this.mostrarAviso = false; }, 1800);
    }
  },
  async mounted() {
    try {
      // Obtiene del backend el producto creado desde el panel administrativo.
      const response = await api.get(`/productos/${this.id}`);
      this.producto = {
        ...response.data,
        name: response.data.nombre,
        price: Number(response.data.precio_venta) || 0
      };
      const relacionados = await api.get('/productos');
      this.productosRelacionados = [...relacionados.data, ...CATALOGO]
        .filter(producto => producto.id !== Number(this.id))
        .slice(0, 3)
        .map(producto => ({
          ...producto,
          name: producto.nombre || producto.name,
          price: Number(producto.precio_venta ?? producto.price) || 0
        }));
    } catch (requestError) {
      console.error('Error al cargar el producto:', requestError);
      // Permite seguir abriendo los productos temporales de la portada.
      this.producto = obtenerProducto(this.id);
      this.productosRelacionados = CATALOGO.filter(producto => producto.id !== this.id).slice(0, 3);
    }
  }
};
</script>
