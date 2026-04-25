import {Navbar} from '@common/ui/navigation/navbar/navbar';
import {Footer} from '@common/ui/footer/footer';
import {StaticPageTitle} from '@common/seo/static-page-title';

export function AppCardPage() {
  return (
    <div className="flex min-h-screen flex-col bg-[#23232c]">
      <StaticPageTitle>Descarga nuestra App</StaticPageTitle>
      
      {/* Navbar original del sistema */}
      <Navbar className="sticky top-0 z-50 flex-shrink-0" />

      {/* Inyectamos tu CSS exacto */}
      <style dangerouslySetInnerHTML={{ __html: `
  .playstore-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #000;
    border-radius: 9999px;
    background-color: rgba(0, 0, 0, 1);
    padding: 0.625rem 1.5rem;
    text-align: center;
    color: rgba(255, 255, 255, 1);
    outline: 0;
    transition: all  .2s ease;
    text-decoration: none;
  }

  .playstore-button:hover {
    background-color: transparent;
    color: rgba(0, 0, 0, 1);
  }

  .icon {
    height: 1.5rem;
    width: 1.5rem;
  }

  .texts {
    margin-left: 1rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1;
  }

  .text-1 {
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    line-height: 1rem;
  }

  .text-2 {
    font-weight: 600;
  }

  /* --- NUEVOS TEXTOS DE ENCABEZADO --- */
  /* --- TÍTULOS FUERA DE LA TARJETA --- */
  .section-header {
    text-align: center;
    margin-bottom: 10px; /* Espacio antes de la tarjeta */
    font-family: 'Inter', sans-serif;
  }

  .main-download-title {
    display: block;
    font-size: 36px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -1.5px;
    margin-bottom: 8px;
  }

  .main-download-subtitle {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #10b981; /* Verde esmeralda a juego con el desarrollador */
    text-transform: uppercase;
    letter-spacing: 4px; /* Estilo muy espaciado y elegante */
    opacity: 0.9;
  }

  .app-card {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centra todo el contenido horizontalmente */
    max-width: 450px; /* Ancho ajustado para formato vertical/alargado */
    margin: 40px auto;
    padding: 35px 25px;
    background-color: #0d0d0d;
    
    /* Fondo sutil: Textura de grano fino o degradado suave */
    background-image: radial-gradient(circle at 50% 0%, #1a1a1a 0%, #0d0d0d 100%);
    
    /* Forma medio cuadrada */
    border-radius: 24px;
    
    /* Shadow profundo y borde de definición */
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05);
    
    font-family: 'Inter', -apple-system, sans-serif;
    text-align: center; /* Centra los textos */
    position: relative; /* Para posicionar el estampado */
    overflow: hidden;
  }

  .app-card::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    /* Patrón de líneas diagonales premium */
    background: repeating-linear-gradient(
      45deg,
      transparent,
      transparent 10px,
      rgba(0, 255, 0, 0.07) 10px,
      rgba(255, 255, 255, 0.04) 11px
    );
    /* Máscara radial para que el estampado solo se vea en el centro (estilo luz) */
    -webkit-mask-image: radial-gradient(circle at 50% 25%, black, transparent 70%);
    mask-image: radial-gradient(circle at 50% 25%, black, transparent 70%);
    pointer-events: none;
    z-index: 0;
  }

  /* Para que el contenido resalte sobre el estampado */
  .app-header, .app-actions {
    position: relative;
    z-index: 1;
  }

  /* --- LOGO EN EL MEDIO ARRIBA --- */
  .app-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
  }

  .app-icon {
    width: 90px;
    height: 90px;
    border-radius: 20px;
    object-fit: cover;
    margin-bottom: 20px; /* Espacio bajo el logo */
    box-shadow: 0 15px 30px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
  }

  .app-info {
    margin-bottom: 30px;
  }

  .app-title {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #ffffff;
    letter-spacing: -0.5px;
  }

  .app-dev {
    margin: 5px 0 12px 0;
    font-size: 14px;
    color: #10b981; /* Un toque de color sutil */
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .app-desc {
    margin: 0 auto;
    font-size: 15px;
    color: #888;
    line-height: 1.6;
    max-width: 90%;
  }

  /* --- CONTENEDOR DE ACCIONES --- */
  .app-actions {
    width: 100%;
    display: flex;
    justify-content: center;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
  }

  /* --- TU BOTÓN (INTACTO) --- */
  .playstore-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #000;
    border-radius: 9999px;
    background-color: rgba(0, 0, 0, 1);
    padding: 0.625rem 1.5rem;
    text-align: center;
    color: rgba(255, 255, 255, 1);
    outline: 0;
    transition: all .2s ease;
    text-decoration: none;
  }

  .playstore-button:hover {
    background-color: transparent;
    color: rgba(0, 0, 0, 1);
    border-color: #000; /* Se mantiene negro según tu código */
  }

  /* Soporte para que el hover se vea en fondo oscuro */
  .app-card .playstore-button:hover {
    background-color: #fff;
    color: #000;
  }

  .icon {
    height: 1.5rem;
    width: 1.5rem;
  }

  .texts {
    margin-left: 1rem;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    line-height: 1;
  }

  .text-1 {
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    line-height: 1rem;
  }

  .text-2 {
    font-weight: 600;
  }
      ` }} />

      {/* Contenido principal con tu estructura HTML */}
      <main className="flex-auto flex items-center justify-center p-24">
<div className="vibeturn-container">
    
    <div className="section-header">
        <h2 className="main-download-title">Descarga Vibeturn</h2>
        <p className="main-download-subtitle">Sonido premium sin límites</p>
    </div>

    <div className="app-card">
        <div className="app-header">
            <img className="app-icon" src="https://play-lh.googleusercontent.com/fWRaHG-FcZ2qt5UES2meD2ChEOOufRTyhdlxg9vF3V0a09vF3mjaWUoZdIIyUK2IWA=w240-h480-rw" alt="App Icon" />
            
            <div className="app-info">
                <h3 className="app-title">Vibeturn: Música sin límites</h3>
                <p className="app-dev">Vibeturn Studios</p>
                <p className="app-desc">Descubre una nueva forma de escuchar tus artistas favoritos con audio de alta fidelidad.</p>
            </div>
        </div>

        <div className="app-actions">
            <a className="playstore-button" href="https://play.google.com/store/apps/details?id=com.openmsucivibes.vibeturn&pli=1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" className="icon" viewBox="0 0 512 512">
                    <path d="M99.617 8.057a50.191 50.191 0 00-38.815-6.713l230.932 230.933 74.846-74.846L99.617 8.057zM32.139 20.116c-6.441 8.563-10.148 19.077-10.148 30.199v411.358c0 11.123 3.708 21.636 10.148 30.199l235.877-235.877L32.139 20.116zM464.261 212.087l-67.266-37.637-81.544 81.544 81.548 81.548 67.273-37.64c16.117-9.03 25.738-25.442 25.738-43.908s-9.621-34.877-25.749-43.907zM291.733 279.711L60.815 510.629c3.786.891 7.639 1.371 11.492 1.371a50.275 50.275 0 0027.31-8.07l266.965-149.372-74.849-74.847z"></path>
                </svg>
                <span className="texts">
                    <span className="text-1">GET IT ON</span>
                    <span className="text-2">Google Play</span>
                </span>
            </a>
        </div>
    </div>

</div>
      </main>

      <Footer className="container mx-auto flex-shrink-0 px-24 mt-auto" />
    </div>
  );
}
