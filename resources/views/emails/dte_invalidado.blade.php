<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>Comprobante de Invalidación DTE</title>
    <!--[if mso]>
  <style>
    table {border-collapse:collapse;border-spacing:0;border:none;margin:0;}
    div, td {padding:0;}
    div {margin:0 !important;}
	</style>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
    <style>
        table,
        td,
        div,
        h1,
        p {
            font-family: Arial, sans-serif;
        }

        @media screen and (max-width: 530px) {
            .unsub {
                display: block;
                padding: 8px;
                margin-top: 14px;
                border-radius: 6px;
                background-color: #555555;
                text-decoration: none !important;
                font-weight: bold;
            }

            .col-lge {
                max-width: 100% !important;
            }
        }

        @media screen and (min-width: 531px) {
            .col-sml {
                max-width: 27% !important;
            }

            .col-lge {
                max-width: 73% !important;
            }
        }
    </style>
</head>

<body style="margin:0;padding:0;word-spacing:normal;background-color:#939297;">
    <div role="article" aria-roledescription="email" lang="es"
         style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#939297;">
        <table role="presentation" style="width:100%;border:none;border-spacing:0;">
            <tr>
                <td align="center" style="padding:0;">
                    <!--[if mso]>
          <table role="presentation" align="center" style="width:600px;">
          <tr>
          <td>
          <![endif]-->
                    <table role="presentation"
                           style="width:94%;max-width:600px;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                        <tr>
                            <td style="padding:40px 30px 30px 30px;text-align:center;font-size:24px;font-weight:bold;background-color:#ffffff;">
                                <a href="#" style="text-decoration:none; color:#dc3545;">DOCUMENTO FISCAL INVALIDADO</a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;background-color:#ffffff;">
                                <h3 style="margin-top:0;margin-bottom:16px;font-size:18px;line-height:32px;font-weight:bold;letter-spacing:-0.02em;">
                                    Estimado/a Cliente: {{$data["nombre"]}}</h3>
                                <p style="margin:0;">
                                    Le informamos que el Documento Tributario Electrónico (DTE) detallado a continuación ha sido <strong>INVALIDADO/ANULADO</strong> en los registros del Ministerio de Hacienda.
                                    <br><br>
                                    Adjunto a este correo encontrará el archivo PDF del documento original y el archivo JSON que certifica la invalidación.
                                </p>
                                <br>
                                <table role="presentation" style="width:100%; border-collapse: collapse; font-size:14px; line-height:20px;">
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;" width="40%">Número de Control Original:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">{{$data["numero_original"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Cód. de Generación Original:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee; font-family: monospace;">{{$data["codigo_generacion_original"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Fecha de Emisión:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">{{$data["fecha_emision_original"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Monto de Operación:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">${{FNumero($data["total_original"])}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 15px 0 6px 0; font-weight: bold; color: #dc3545;" colspan="2">DETALLE DE LA INVALIDACIÓN</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Número de Control de la Invalidación:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">{{$data["numero_original"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Cód. Generación Invalidación:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee; font-family: monospace;">{{$data["codigo_generacion_invalidacion"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Sello de Invalidación:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee; font-family: monospace;">{{$data["sello_recibido_invalidacion"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Fecha de Invalidación:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">{{$data["fecha_invalidacion"]}}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; font-weight: bold; border-bottom: 1px solid #eeeeee;">Motivo:</td>
                                        <td style="padding: 6px 0; border-bottom: 1px solid #eeeeee;">{{$data["motivo"]}}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 10px 30px; background-color: #ffffff;">
                                <p style="color:red; font-size:12px; margin:0;">Este correo fue generado automáticamente, favor no contestar. Si tiene alguna duda o consulta, comuníquese directamente con su proveedor.</p>
                                <br>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;text-align:center;font-size:12px;background-color:#404040;color:#cccccc;">
                                <p style="margin:0;font-size:14px;line-height:20px;">&reg; Powered by Bethel Travel &copy; 2026<br></p>
                            </td>
                        </tr>
                    </table>
                    <!--[if mso]>
          </td>
          </tr>
          </table>
          <![endif]-->
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
