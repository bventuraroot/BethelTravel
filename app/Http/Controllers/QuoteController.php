<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Sale;
use App\Models\Salesdetail;
use App\Models\Address;
use App\Models\Phone;
use App\Mail\QuoteMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company_selected = $request->session()->get('company_selected') ?? 1;
        $isAdmin = auth()->user()->hasRole('Admin') || auth()->user()->can('manage_users');
        
        $query = Quote::with(['company', 'client', 'user'])
            ->where('company_id', $company_selected);
            
        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }
        
        $quotes = $query->orderBy('id', 'desc')->get();
        
        return view('quotes.index', compact('quotes', 'company_selected'));
    }

    private function getUniqueHotels()
    {
        $hotels = [];
        $quotesWithHotels = DB::table('quotes')->whereNotNull('hotels_grid')->get();
        foreach ($quotesWithHotels as $q) {
            $grid = json_decode($q->hotels_grid, true);
            if (!empty($grid['rows'])) {
                foreach ($grid['rows'] as $row) {
                    if (!empty($row['hotel'])) {
                        $hotels[] = $row['hotel'];
                    }
                }
            }
        }
        
        $defaultHotels = [
            'Hotel Sociatel Medellin***',
            'Loyds Hotel***',
            'Hotel The Morgana Poblado Suites****',
            'V Grand Hotel, A Member Of Radisson Individuals****',
            'Hotel Dann Carlton Medellín*****',
            'Hotel San Fernando Plaza*****',
            'Hotel Estelar Milla de Oro*****',
            'Decameron Galeón (Santa Marta)',
            'Decameron Aquarium (San Andrés)',
            'Hotel Riu Plaza España (Madrid)',
            'Hard Rock Hotel Cancún'
        ];
        
        $allHotels = array_unique(array_merge($hotels, $defaultHotels));
        sort($allHotels);
        
        return array_values($allHotels);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company_selected = $request->session()->get('company_selected') ?? 1;
        $clients = Client::where('company_id', $company_selected)->get();
        $airlines = DB::table('aerolineas')->orderBy('nombre', 'asc')->get();
        $airports = DB::table('aeropuertos')->where('iata', '!=', 'NA')->orderBy('ciudad', 'asc')->get();
        $allHotels = $this->getUniqueHotels();
        
        return view('quotes.create', compact('clients', 'airlines', 'airports', 'allHotels', 'company_selected'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'company_selected' => 'required',
        ]);

        $company_selected = $request->input('company_selected');
        
        // Handle banner images (predefined defaults or custom uploads)
        $bannerImages = [];
        $predefined = $request->input('predefined_destination');
        
        if ($predefined === 'medellin') {
            $bannerImages = [
                'assets/img/quotes/defaults/medellin_guatape.png',
                'assets/img/quotes/defaults/medellin_skyline.png',
                'assets/img/quotes/defaults/medellin_comuna13.png'
            ];
        } elseif ($request->hasFile('banner_images')) {
            if (!file_exists(public_path('assets/img/quotes'))) {
                mkdir(public_path('assets/img/quotes'), 0755, true);
            }
            
            foreach ($request->file('banner_images') as $file) {
                if (count($bannerImages) >= 3) break;
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/quotes'), $filename);
                $bannerImages[] = 'assets/img/quotes/' . $filename;
            }
        }
        
        // Compile Inclusions
        $includes = array_filter($request->input('includes', []));

        // Compile Hotels Grid
        $cols = $request->input('hotels_grid_cols', []);
        $rows = [];
        $rawRows = $request->input('hotels_grid_rows', []);
        if (is_array($rawRows)) {
            foreach ($rawRows as $index => $rowName) {
                if (empty($rowName)) continue;
                $prices = [];
                foreach ($cols as $col) {
                    $prices[$col] = $request->input("hotels_grid_prices.{$index}.{$col}", 0);
                }
                $rows[] = [
                    'hotel' => $rowName,
                    'prices' => $prices
                ];
            }
        }
        
        $hotelsGrid = [
            'title' => $request->input('hotels_grid_title', 'HOTELES Y TARIFAS'),
            'columns' => $cols,
            'rows' => $rows,
            'footer' => $request->input('hotels_grid_footer', '')
        ];

        // Compile Flights
        $flights = [];
        $rawFlights = $request->input('flights', []);
        if (is_array($rawFlights)) {
            foreach ($rawFlights as $f) {
                if (empty($f['flight_number'])) continue;
                
                // Get airline name
                $airlineName = 'Vuelo';
                if (!empty($f['airline_code'])) {
                    $dbAirline = DB::table('aerolineas')->where('iata', $f['airline_code'])->first();
                    if ($dbAirline) {
                        $airlineName = $dbAirline->nombre;
                    }
                }
                
                $originName = '';
                if (!empty($f['origin_code'])) {
                    $ap = DB::table('aeropuertos')->where('iata', $f['origin_code'])->first();
                    if ($ap) {
                        $originName = $ap->ciudad . ' (' . $ap->iata . ')';
                    }
                }
                
                $destName = '';
                if (!empty($f['destination_code'])) {
                    $ap = DB::table('aeropuertos')->where('iata', $f['destination_code'])->first();
                    if ($ap) {
                        $destName = $ap->ciudad . ' (' . $ap->iata . ')';
                    }
                }

                $flights[] = [
                    'airline_code' => $f['airline_code'] ?? '',
                    'airline_name' => $airlineName,
                    'flight_number' => $f['flight_number'] ?? '',
                    'origin_code' => $f['origin_code'] ?? '',
                    'origin_name' => $originName ?: ($f['origin_code'] ?? ''),
                    'departure_date' => $f['departure_date'] ?? null,
                    'departure_time' => $f['departure_time'] ?? '',
                    'destination_code' => $f['destination_code'] ?? '',
                    'destination_name' => $destName ?: ($f['destination_code'] ?? ''),
                    'arrival_date' => $f['arrival_date'] ?? null,
                    'arrival_time' => $f['arrival_time'] ?? ''
                ];
            }
        }

        // Compile Notes
        $notes = array_filter($request->input('notes', []));

        // Create Quote
        Quote::create([
            'company_id' => $company_selected,
            'client_id' => $request->input('client_id') !== 'new' ? $request->input('client_id') : null,
            'client_name' => $request->input('client_name'),
            'client_email' => $request->input('client_email'),
            'client_phone' => $request->input('client_phone'),
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'banner_images' => $bannerImages,
            'includes' => $includes,
            'hotels_grid' => $hotelsGrid,
            'flights' => $flights,
            'notes' => $notes,
            'status' => 'draft',
            'user_id' => Auth::id()
        ]);

        return redirect()->route('quote.index')->with('success', 'Cotización creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        $company_selected = $request->session()->get('company_selected') ?? $quote->company_id;
        $clients = Client::where('company_id', $company_selected)->get();
        $airlines = DB::table('aerolineas')->orderBy('nombre', 'asc')->get();
        $airports = DB::table('aeropuertos')->where('iata', '!=', 'NA')->orderBy('ciudad', 'asc')->get();
        $allHotels = $this->getUniqueHotels();
        
        return view('quotes.edit', compact('quote', 'clients', 'airlines', 'airports', 'allHotels', 'company_selected'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Banner images
        $bannerImages = $quote->banner_images ?? [];
        $predefined = $request->input('predefined_destination');
        
        if ($predefined === 'medellin') {
            $bannerImages = [
                'assets/img/quotes/defaults/medellin_guatape.png',
                'assets/img/quotes/defaults/medellin_skyline.png',
                'assets/img/quotes/defaults/medellin_comuna13.png'
            ];
        } elseif ($request->hasFile('banner_images')) {
            // Delete old uploaded custom banners if replacing
            foreach ($bannerImages as $oldImg) {
                if (str_contains($oldImg, 'assets/img/quotes/') && !str_contains($oldImg, '/defaults/')) {
                    if (file_exists(public_path($oldImg))) {
                        @unlink(public_path($oldImg));
                    }
                }
            }
            
            $bannerImages = [];
            if (!file_exists(public_path('assets/img/quotes'))) {
                mkdir(public_path('assets/img/quotes'), 0755, true);
            }
            
            foreach ($request->file('banner_images') as $file) {
                if (count($bannerImages) >= 3) break;
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('assets/img/quotes'), $filename);
                $bannerImages[] = 'assets/img/quotes/' . $filename;
            }
        }
        
        // Compile Inclusions
        $includes = array_filter($request->input('includes', []));

        // Compile Hotels Grid
        $cols = $request->input('hotels_grid_cols', []);
        $rows = [];
        $rawRows = $request->input('hotels_grid_rows', []);
        if (is_array($rawRows)) {
            foreach ($rawRows as $index => $rowName) {
                if (empty($rowName)) continue;
                $prices = [];
                foreach ($cols as $col) {
                    $prices[$col] = $request->input("hotels_grid_prices.{$index}.{$col}", 0);
                }
                $rows[] = [
                    'hotel' => $rowName,
                    'prices' => $prices
                ];
            }
        }
        
        $hotelsGrid = [
            'title' => $request->input('hotels_grid_title', 'HOTELES Y TARIFAS'),
            'columns' => $cols,
            'rows' => $rows,
            'footer' => $request->input('hotels_grid_footer', '')
        ];

        // Compile Flights
        $flights = [];
        $rawFlights = $request->input('flights', []);
        if (is_array($rawFlights)) {
            foreach ($rawFlights as $f) {
                if (empty($f['flight_number'])) continue;
                
                $airlineName = 'Vuelo';
                if (!empty($f['airline_code'])) {
                    $dbAirline = DB::table('aerolineas')->where('iata', $f['airline_code'])->first();
                    if ($dbAirline) {
                        $airlineName = $dbAirline->nombre;
                    }
                }
                
                $originName = '';
                if (!empty($f['origin_code'])) {
                    $ap = DB::table('aeropuertos')->where('iata', $f['origin_code'])->first();
                    if ($ap) {
                        $originName = $ap->ciudad . ' (' . $ap->iata . ')';
                    }
                }
                
                $destName = '';
                if (!empty($f['destination_code'])) {
                    $ap = DB::table('aeropuertos')->where('iata', $f['destination_code'])->first();
                    if ($ap) {
                        $destName = $ap->ciudad . ' (' . $ap->iata . ')';
                    }
                }

                $flights[] = [
                    'airline_code' => $f['airline_code'] ?? '',
                    'airline_name' => $airlineName,
                    'flight_number' => $f['flight_number'] ?? '',
                    'origin_code' => $f['origin_code'] ?? '',
                    'origin_name' => $originName ?: ($f['origin_code'] ?? ''),
                    'departure_date' => $f['departure_date'] ?? null,
                    'departure_time' => $f['departure_time'] ?? '',
                    'destination_code' => $f['destination_code'] ?? '',
                    'destination_name' => $destName ?: ($f['destination_code'] ?? ''),
                    'arrival_date' => $f['arrival_date'] ?? null,
                    'arrival_time' => $f['arrival_time'] ?? ''
                ];
            }
        }

        // Compile Notes
        $notes = array_filter($request->input('notes', []));

        $quote->update([
            'client_id' => $request->input('client_id') !== 'new' ? $request->input('client_id') : null,
            'client_name' => $request->input('client_name'),
            'client_email' => $request->input('client_email'),
            'client_phone' => $request->input('client_phone'),
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'banner_images' => $bannerImages,
            'includes' => $includes,
            'hotels_grid' => $hotelsGrid,
            'flights' => $flights,
            'notes' => $notes,
        ]);

        return redirect()->route('quote.index')->with('success', 'Cotización actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        
        // Delete banner images
        if (is_array($quote->banner_images)) {
            foreach ($quote->banner_images as $img) {
                if (str_contains($img, 'assets/img/quotes/') && !str_contains($img, '/defaults/')) {
                    if (file_exists(public_path($img))) {
                        @unlink(public_path($img));
                    }
                }
            }
        }
        
        $quote->delete();
        return redirect()->route('quote.index')->with('success', 'Cotización eliminada correctamente.');
    }

    /**
     * Generate PDF in browser stream
     */
    public function generatePdf($id)
    {
        $quote = Quote::with(['company', 'user'])->findOrFail($id);
        
        $bannerImagesPaths = [];
        if (is_array($quote->banner_images)) {
            foreach ($quote->banner_images as $img) {
                $bannerImagesPaths[] = public_path(ltrim($img, '/'));
            }
        }
        
        $logoPath = $quote->company && $quote->company->logo ? public_path('assets/img/logo/' . $quote->company->logo) : null;
        
        $pdf = app('dompdf.wrapper');
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->setPaper('Letter', 'Portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        
        $pdf->loadView('quotes.pdf', compact('quote', 'bannerImagesPaths', 'logoPath'));
        
        $filename = 'Cotizacion_' . str_replace(' ', '_', $quote->title) . '.pdf';
        return $pdf->stream($filename);
    }

    /**
     * Send email with PDF attached
     */
    public function sendEmail(Request $request, $id)
    {
        $quote = Quote::with(['company', 'user'])->findOrFail($id);
        
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $bannerImagesPaths = [];
        if (is_array($quote->banner_images)) {
            foreach ($quote->banner_images as $img) {
                $bannerImagesPaths[] = public_path(ltrim($img, '/'));
            }
        }
        $logoPath = $quote->company && $quote->company->logo ? public_path('assets/img/logo/' . $quote->company->logo) : null;
        
        // Generate PDF
        $pdf = app('dompdf.wrapper');
        $pdf->set_option('isHtml5ParserEnabled', true);
        $pdf->set_option('isRemoteEnabled', true);
        $pdf->setPaper('Letter', 'Portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        
        $pdf->loadView('quotes.pdf', compact('quote', 'bannerImagesPaths', 'logoPath'));
        $pdfData = $pdf->output();
        
        $filename = 'Cotizacion_' . str_replace(' ', '_', $quote->title) . '.pdf';
        
        // Send mail
        try {
            Mail::to($request->email)->send(new QuoteMail(
                $quote,
                $pdfData,
                $filename,
                $request->subject,
                $request->body
            ));
            
            $quote->update(['status' => 'sent']);
            
            return response()->json(['success' => true, 'message' => 'El correo ha sido enviado correctamente con la cotización adjunta.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Ocurrió un error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show conversion screen
     */
    public function showConvertToSale(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        $company_selected = $request->session()->get('company_selected') ?? $quote->company_id;
        
        $clients = Client::where('company_id', $company_selected)->get();
        $products = Product::where('state', 1)->get();
        
        return view('quotes.convert_to_sale', compact('quote', 'clients', 'products', 'company_selected'));
    }

    /**
     * Store sale from quote
     */
    public function storeConvertToSale(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);
        
        $request->validate([
            'client_selection' => 'required',
            'product_id' => 'required|exists:products,id',
            'selected_price' => 'required|numeric',
            'selected_hotel' => 'required|string',
            'selected_occupancy' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $clientId = $request->input('client_selection');
            $companyId = $quote->company_id;
            
            // 1. Create client if checking "new client"
            if ($clientId === 'new') {
                $request->validate([
                    'new_firstname' => 'required|string',
                    'new_firstlastname' => 'required|string',
                    'new_nit' => 'required|string',
                    'new_tpersona' => 'required|in:N,J,E',
                ]);
                
                $phone = new Phone();
                $phone->phone = $request->input('new_phone', $quote->client_phone ?? 'N/A');
                $phone->save();
                
                $address = new Address();
                $address->country_id = 1; // Default
                $address->save();
                
                $client = new Client();
                $client->firstname = $request->input('new_firstname');
                $client->firstlastname = $request->input('new_firstlastname');
                $client->nit = str_replace(['-', ' '], '', $request->input('new_nit'));
                $client->email = $request->input('new_email', $quote->client_email);
                $client->tpersona = $request->input('new_tpersona');
                $client->contribuyente = '0';
                $client->extranjero = $request->input('new_tpersona') === 'E' ? '1' : '0';
                $client->agente_retencion = '0';
                $client->company_id = $companyId;
                $client->address_id = $address->id;
                $client->phone_id = $phone->id;
                $client->user_id = Auth::id();
                $client->save();
                
                $clientId = $client->id;
            }
            
            // Get Typedocument for ticket/sale (usually standard ticket is FCF - Factura Consumidor Final, or REC)
            // Let's find any valid typedocument for this company
            $typedoc = DB::table('typedocuments')
                ->where('company_id', $companyId)
                ->where('type', 'FCF') // Factura Consumidor Final default
                ->first();
                
            if (!$typedoc) {
                // fallback to any
                $typedoc = DB::table('typedocuments')
                    ->where('company_id', $companyId)
                    ->first();
            }
            
            // Get correlativo
            $nu_doc = '00001';
            if ($typedoc) {
                $docCorr = DB::table('docs')
                    ->where('id_tipo_doc', $typedoc->type)
                    ->where('id_empresa', $companyId)
                    ->first();
                if ($docCorr) {
                    $nu_doc = $docCorr->actual;
                    DB::table('docs')->where('id', $docCorr->id)->increment('actual');
                }
            }

            // 2. Create Sale
            $sale = new Sale();
            $sale->company_id = $companyId;
            $sale->client_id = $clientId;
            $sale->user_id = Auth::id();
            $sale->date = now();
            $sale->typesale = 2; // Borrador (Draft) initially, finalized upon DTE issuance
            $sale->state = 1; // Active
            $sale->state_credit = 0;
            $sale->waytopay = 1; // cash/efectivo default
            $sale->totalamount = $request->input('selected_price');
            $sale->typedocument_id = $typedoc ? $typedoc->id : 1;
            $sale->nu_doc = $nu_doc;
            $sale->nu_unico = time() . rand(100, 999);
            $sale->save();
            
            // 3. Create Salesdetail
            $product = DB::table('products')->where('id', $request->input('product_id'))->first();
            $isGravado = $product && $product->cfiscal === 'gravado';
            $priceSale = $request->input('selected_price');
            
            $detail = new Salesdetail();
            $detail->sale_id = $sale->id;
            $detail->product_id = $request->input('product_id');
            $detail->amountp = 1;
            
            if ($isGravado) {
                $priceWithoutIva = round($priceSale / 1.13, 8);
                $ivaVal = round($priceSale - $priceWithoutIva, 8);
                
                $detail->priceunit = $priceWithoutIva;
                $detail->pricesale = $priceWithoutIva;
                $detail->nosujeta = 0.00;
                $detail->exempt = 0.00;
                $detail->detained13 = $ivaVal;
            } else {
                $detail->priceunit = $priceSale;
                $detail->pricesale = 0.00;
                $detail->nosujeta = 0.00;
                $detail->exempt = $priceSale;
                $detail->detained13 = 0.00;
            }
            
            $detail->detained = 0.00;
            $detail->detainedP = 0;
            $detail->renta = 0.00;
            $detail->fee = 0.00;
            $detail->feeiva = 0.00;
            $detail->linea = null;
            $detail->canal = null;
            
            $detail->description = "Paquete turístico a: " . $quote->title . " - Hotel: " . $request->input('selected_hotel') . " (" . $request->input('selected_occupancy') . ")";
            
            // Copy flight details if any
            if (!empty($quote->flights) && isset($quote->flights[0])) {
                $detail->reserva = $quote->flights[0]['flight_number'] ?? '';
                $detail->ruta = ($quote->flights[0]['origin_code'] ?? '') . '-' . ($quote->flights[0]['destination_code'] ?? '');
                $detail->destino = $quote->flights[0]['destination_name'] ?? '';
                $detail->fecha_viaje = $quote->flights[0]['departure_date'] ?? null;
            }
            
            $detail->user_id = Auth::id();
            $detail->save();
            
            // 4. Update Quote Status
            $quote->update(['status' => 'approved', 'client_id' => $clientId]);
            
            DB::commit();
            return redirect()->route('sale.index')->with('success', 'Cotización convertida en Venta nº ' . $nu_doc . ' con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al convertir la cotización: ' . $e->getMessage())->withInput();
        }
    }
}
