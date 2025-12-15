import requests

def obtener_contenido_para_ia(base_url: str) -> tuple[str, str]:
    """
    Devuelve (origen, contenido):
    - origen: "LLMS" si ha usado llms.txt, "HTML" si ha usado la página normal
    - contenido: texto que vería el LLM
    """
    base_url = base_url.rstrip("/")

    llms_url = base_url + "/llms.txt"
    print(f"[+] Probando primero: {llms_url}")

    try:
        r = requests.get(llms_url, timeout=10)
    except Exception as e:
        print(f"[!] Error al pedir llms.txt: {e}")
        r = None

    if r is not None and r.status_code == 200 and r.text.strip():
        print("[+] Encontrado llms.txt, el agente CONFIARÁ ciegamente en este archivo.")
        return "LLMS", r.text

    print("[!] No hay llms.txt útil, usando HTML de la página.")
    try:
        r2 = requests.get(base_url, timeout=10)
        if r2.status_code == 200:
            return "HTML", r2.text
        else:
            return "ERROR", f"Status code HTML: {r2.status_code}"
    except Exception as e:
        return "ERROR", f"Error al pedir HTML: {e}"

if __name__ == "__main__":
    url = "https://abelgd.github.io/cafe-laesquina/"
    origen, contenido = obtener_contenido_para_ia(url)

    print(contenido[:4000])