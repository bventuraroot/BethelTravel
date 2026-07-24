<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Client;
use App\Models\Company;
use App\Models\Phone;
use App\Http\Requests\ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Nette\Utils\Arrays;
use Spatie\Permission\Traits\HasRoles;

use function PHPUnit\Framework\isNull;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $company = "0")
    {
        $id_user = auth()->user()->id;
        $scope = $request->get('scope', 'all'); // Por defecto 'all' (Todos los Clientes) o 'my'

        // Obtener la empresa a la que pertenece el usuario
        $company_user = Company::join('permission_company', 'companies.id', '=', 'permission_company.company_id')
            ->where('permission_company.user_id', '=', $id_user)
            ->pluck('companies.id')
            ->first();

        if (!$company_user) {
            $company_user = Company::value('id') ?? 1;
        }

        $company_selected = ($company != "0") ? base64_decode($company) : $company_user;

        // Consultar el rol del usuario (admin=1 y contabilidad=2 como en RomaCopies)
        $rolQuery = "SELECT a.role_id FROM model_has_roles a WHERE a.model_id = ?";
        $rolResult = DB::select($rolQuery, [$id_user]);
        $isAdmin = !empty($rolResult) && ($rolResult[0]->role_id == 1 || $rolResult[0]->role_id == 2);

        // Construcción de la consulta
        $clientsQuery = Client::join('addresses', 'clients.address_id', '=', 'addresses.id')
            ->join('countries', 'addresses.country_id', '=', 'countries.id')
            ->leftJoin('departments', 'addresses.department_id', '=', 'departments.id')
            ->leftJoin('municipalities', 'addresses.municipality_id', '=', 'municipalities.id')
            ->leftJoin('economicactivities', 'clients.economicactivity_id', '=', 'economicactivities.id')
            ->join('phones', 'clients.phone_id', '=', 'phones.id')
            ->leftJoin('users', 'clients.user_id', '=', 'users.id')
            ->select(
                'clients.*',
                'countries.name as pais',
                'departments.name as departamento',
                'municipalities.name as municipioname',
                'economicactivities.name as econo',
                'addresses.reference as address',
                'phones.phone',
                'phones.phone_fijo',
                'addresses.country_id as country',
                'addresses.department_id as departament',
                'addresses.municipality_id as municipio',
                'clients.economicactivity_id as acteconomica',
                'users.name as creador_nombre'
            )
            ->where('clients.company_id', $company_selected);

        // Si el filtro está en 'my' y el usuario no es admin, se muestran solo los suyos.
        // Si el filtro está en 'all' o es admin, muestra todos los clientes de la empresa.
        if ($scope === 'my' && !$isAdmin) {
            $clientsQuery->where('clients.user_id', $id_user);
        }

        // Obtener los clientes filtrados
        $clients = $clientsQuery->get();

        return view('client.index', [
            "clients" => $clients,
            "companyselected" => $company_selected,
            "scope" => $scope,
            "isAdmin" => $isAdmin
        ]);
    }



    public function getclientbycompany($idcompany)
    {
        $query = "SELECT
            a.id,
            a.tpersona,
            a.firstname,
            a.secondname,
            a.firstlastname,
            a.secondlastname,
            a.comercial_name,
            a.name_contribuyente,
            a.nit,
            a.ncr,
            a.email,
            a.extranjero,
            a.pasaporte,
            a.contribuyente,
            (CASE a.tpersona
                WHEN 'J' THEN COALESCE(NULLIF(a.name_contribuyente,''), a.comercial_name)
                ELSE TRIM(CONCAT_WS(' ',
                    NULLIF(a.firstname,''),
                    NULLIF(a.secondname,''),
                    NULLIF(a.firstlastname,''),
                    NULLIF(a.secondlastname,'')
                ))
            END) AS name_format_label
        FROM clients a
        WHERE a.company_id = ?
        ORDER BY name_format_label ASC";

        $result = DB::select($query, [base64_decode($idcompany)]);
        return response()->json($result);
    }

    public function gettypecontri($client)
    {
        $id = base64_decode($client);
        $contribuyente = Client::leftJoin('addresses', 'clients.address_id', '=', 'addresses.id')
            ->leftJoin('countries', 'addresses.country_id', '=', 'countries.id')
            ->leftJoin('departments', 'addresses.department_id', '=', 'departments.id')
            ->leftJoin('municipalities', 'addresses.municipality_id', '=', 'municipalities.id')
            ->leftJoin('economicactivities', 'clients.economicactivity_id', '=', 'economicactivities.id')
            ->leftJoin('phones', 'clients.phone_id', '=', 'phones.id')
            ->select(
                'clients.*',
                'countries.name as pais_name',
                'departments.name as departamento_name',
                'municipalities.name as municipio_name',
                'economicactivities.name as econo_name',
                'economicactivities.code as econo_code',
                'addresses.reference as address_ref',
                'phones.phone as phone_mobile',
                'phones.phone_fijo as phone_fijo'
            )
            ->where('clients.id', $id)
            ->first();

        if (!$contribuyente) {
            $contribuyente = Client::find($id);
        }

        return response()->json($contribuyente);
    }

    public function keyclient(Request $request)
    {
        $num = trim((string) $request->input('num'));
        $tpersona = $request->input('tpersona') ?: 'N';
        $companyId = $request->input('company_id');
        $clientId = $request->input('client_id'); // Para edición

        // Si la empresa es 0 o no válida, obtener la empresa por defecto del usuario
        if (empty($companyId) || $companyId === '0' || $companyId === 'selectcompany') {
            $companyId = Company::join('permission_company', 'companies.id', '=', 'permission_company.company_id')
                ->where('permission_company.user_id', '=', auth()->id())
                ->pluck('companies.id')
                ->first() ?: Company::value('id');
        }

        $cleanNum = preg_replace('/[^a-zA-Z0-9]/', '', $num);
        $cliente = null;
        $message = '';

        if ($tpersona === "E") {
            // Extranjero - validar pasaporte
            $query = Client::where(function ($q) use ($num, $cleanNum) {
                    $q->where('pasaporte', $num)
                      ->orWhere('pasaporte', $cleanNum)
                      ->orWhereRaw("REPLACE(REPLACE(COALESCE(pasaporte, ''), '-', ''), ' ', '') = ?", [$cleanNum]);
                });

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            if ($clientId) {
                $query->where('id', '!=', $clientId);
            }

            $cliente = $query->first();
            $message = $cliente ? 'El pasaporte ya está registrado para otro cliente en esta empresa.' : 'El pasaporte está disponible.';

        } elseif ($tpersona === "J") {
            // Persona jurídica - validar NIT
            $query = Client::where(function ($q) use ($num, $cleanNum) {
                    $q->where('nit', $num)
                      ->orWhere('nit', $cleanNum)
                      ->orWhereRaw("REPLACE(REPLACE(COALESCE(nit, ''), '-', ''), ' ', '') = ?", [$cleanNum]);
                });

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            if ($clientId) {
                $query->where('id', '!=', $clientId);
            }

            $cliente = $query->first();
            $message = $cliente ? 'El NIT ya está registrado para otra persona jurídica en esta empresa.' : 'El NIT está disponible.';

        } else {
            // Persona natural / Por defecto - validar DUI (nit)
            $query = Client::where(function ($q) use ($num, $cleanNum) {
                    $q->where('nit', $num)
                      ->orWhere('nit', $cleanNum)
                      ->orWhereRaw("REPLACE(REPLACE(COALESCE(nit, ''), '-', ''), ' ', '') = ?", [$cleanNum]);
                });

            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            if ($clientId) {
                $query->where('id', '!=', $clientId);
            }

            $cliente = $query->first();
            $message = $cliente ? 'El DUI ya está registrado para otra persona en esta empresa.' : 'El DUI está disponible.';
        }

        return response()->json([
            'val' => $cliente ? true : false,
            'message' => $message,
            'exists' => $cliente ? true : false
        ]);
    }

    /**
     * Validar NCR específicamente para personas jurídicas
     */
    public function validateNcr(Request $request)
    {
        $ncr = trim((string) $request->input('ncr'));
        $companyId = $request->input('company_id');
        $clientId = $request->input('client_id'); // Para edición

        if (!$ncr || $ncr === 'N/A') {
            return response()->json([
                'val' => false,
                'message' => 'NCR no proporcionado',
                'exists' => false
            ]);
        }

        if (empty($companyId) || $companyId === '0' || $companyId === 'selectcompany') {
            $companyId = Company::join('permission_company', 'companies.id', '=', 'permission_company.company_id')
                ->where('permission_company.user_id', '=', auth()->id())
                ->pluck('companies.id')
                ->first() ?: Company::value('id');
        }

        $cleanNcr = preg_replace('/[^a-zA-Z0-9]/', '', $ncr);

        $query = Client::where(function ($q) use ($ncr, $cleanNcr) {
                $q->where('ncr', $ncr)
                  ->orWhere('ncr', $cleanNcr)
                  ->orWhereRaw("REPLACE(REPLACE(COALESCE(ncr, ''), '-', ''), ' ', '') = ?", [$cleanNcr]);
            });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($clientId) {
            $query->where('id', '!=', $clientId);
        }

        $cliente = $query->first();

        return response()->json([
            'val' => $cliente ? true : false,
            'message' => $cliente ? 'El NCR ya está registrado para otra persona jurídica en esta empresa.' : 'El NCR está disponible.',
            'exists' => $cliente ? true : false
        ]);
    }

    public function getClientid($id)
    {
        $Client = Client::join('addresses', 'clients.address_id', '=', 'addresses.id')
            ->join('countries', 'addresses.country_id', '=', 'countries.id')
            ->leftJoin('departments', 'addresses.department_id', '=', 'departments.id')
            ->leftJoin('municipalities', 'addresses.municipality_id', '=', 'municipalities.id')
            ->leftJoin('economicactivities', 'clients.economicactivity_id', '=', 'economicactivities.id')
            ->join('phones', 'clients.phone_id', '=', 'phones.id')
            ->select(
                'clients.*',
                'countries.name as pais',
                'departments.name as departamento',
                'municipalities.name as municipio',
                'economicactivities.name as econo',
                'addresses.reference as address',
                'phones.phone',
                'phones.phone_fijo',
                'addresses.country_id as country',
                'addresses.department_id as departament',
                'addresses.municipality_id as municipio',
                'clients.economicactivity_id as acteconomica'
            )
            ->where('clients.id', '=', base64_decode($id))
            ->get();
        return response()->json($Client);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('client.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ClientRequest $request)
    {
        DB::beginTransaction();
        try {
            $id_user = auth()->user()->id;
            $phone = new Phone();
            $phone->phone = $request->tel1;
            $phone->phone_fijo = $request->tel2;
            $phone->save();

            $address = new Address();
            $address->country_id = $request->country;
            $address->department_id = (!empty($request->departament) && $request->departament != '0') ? $request->departament : null;
            $address->municipality_id = (!empty($request->municipio) && $request->municipio != '0') ? $request->municipio : null;
            $address->reference = $request->address;
            $address->save();
            //dd($request);
            $client = new Client();
            $client->firstname = (is_null($request->firstname) ? 'N/A' : $request->firstname);
            $client->secondname = (is_null($request->secondname) ? 'N/A' : $request->secondname);
            $client->firstlastname = (is_null($request->firstlastname) ? 'N/A' : $request->firstlastname);
            $client->secondlastname = (is_null($request->secondlastname) ? 'N/A' : $request->secondlastname);
            $client->comercial_name = (is_null($request->comercial_name) ? 'N/A' : $request->comercial_name);
            $client->name_contribuyente = (is_null($request->name_contribuyente) ? 'N/A' : $request->name_contribuyente);
            $client->email = $request->email;
            if ($request->contribuyente == 'on') {
                $contri = '1';
            } else {
                $contri = '0';
            }
            if ($request->extranjero == 'on') {
                $extranjero = '1';
            } else {
                $extranjero = '0';
            }
            if ($request->agente_retencion == 'on') {
                $agente_retencion = '1';
            } else {
                $agente_retencion = '0';
            }
            $client->ncr = (is_null($request->ncr) ? 'N/A' : str_replace(['-', ' '], '', $request->ncr));
            $client->giro = (is_null($request->giro) ? 'N/A' : $request->giro);
            $client->nit = str_replace(['-', ' '], '', $request->nit);
            $client->legal = (is_null($request->legal) ? 'N/A' : $request->legal);
            $client->tpersona = $request->tpersona;
            $client->contribuyente = $contri;
            $client->extranjero = $extranjero;
            $client->agente_retencion = $agente_retencion;
            $client->pasaporte = str_replace(['-', ' '], '', $request->pasaporte);
            $client->tipoContribuyente = $request->tipocontribuyente;
            $client->economicactivity_id = $request->acteconomica;
            $client->birthday = date('Ymd', strtotime($request->birthday));
            $client->empresa = (is_null($request->empresa) ? 'N/A' : $request->empresa);
            $client->company_id = $request->companyselected;
            $client->address_id = $address['id'];
            $client->phone_id = $phone['id'];
            $client->user_id = $id_user;
            $client->save();
            $com = $request->companyselected;
            DB::commit();
            return redirect()->route('client.index', base64_encode($com));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'No se pudo guardar el cliente', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('Company.view', array(
            "company" => Client::join('addresses', 'companies.address_id', '=', 'addresses.id')
                ->join('countries', 'addresses.country_id', '=', 'countries.id')
                ->join('departments', 'addresses.department_id', '=', 'departments.id')
                ->join('municipalities', 'addresses.municipality_id', '=', 'municipalities.id')
                ->join('economicactivities', 'companies.economicactivity_id', '=', 'economicactivities.id')
                ->select('companies.*', 'countries.name as pais', 'departments.name as departamento', 'municipalities.name as municipio', 'economicactivities.name as econo', 'addresses.reference as address')
                ->where('companies.id', '=', $id)
                ->get()
        ));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        //return view('client.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(ClientRequest $request)
    {
        try {
            $id_user = auth()->user()->id;
            $phone = Phone::find($request->phoneeditid);
            $phone->phone = $request->tel1edit;
            $phone->phone_fijo = $request->tel2edit;
            $phone->save();

            $address = Address::find($request->addresseditid);
            $address->country_id = $request->countryedit;
            $address->department_id = (!empty($request->departamentedit) && $request->departamentedit != '0') ? $request->departamentedit : null;
            $address->municipality_id = (!empty($request->municipioedit) && $request->municipioedit != '0') ? $request->municipioedit : null;
            $address->reference = $request->addressedit;
            $address->save();

            // Buscar el cliente usando el ID del campo idedit
            $client = Client::find($request->idedit);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            $client->firstname = $request->firstnameedit;
            $client->secondname = $request->secondnameedit;
            $client->firstlastname = $request->firstlastnameedit;
            $client->secondlastname = $request->secondlastnameedit;
            $client->comercial_name = $request->comercial_nameedit;
            $client->name_contribuyente = $request->name_contribuyenteedit;
            $client->email = $request->emailedit;
            $client->ncr = str_replace(['-', ' '], '', $request->ncredit);
            $client->giro = $request->giroedit;
            $client->nit = str_replace(['-', ' '], '', $request->nitedit);
            $client->legal = $request->legaledit;
            $client->tpersona = $request->tpersonaedit;
            $client->contribuyente = $request->contribuyenteeditvalor;

            // Usar el campo oculto como respaldo para extranjero
            $extranjero_value = $request->extranjeroedit == 'on' ? '1' : ($request->extranjeroedit_hidden == '1' ? '1' : '0');
            $client->extranjero = $extranjero_value;

            // Debug: verificar qué está recibiendo
            \Log::info('agente_retencionedit value: ' . $request->agente_retencionedit);
            \Log::info('agente_retencionedit_hidden value: ' . $request->agente_retencionedit_hidden);
            \Log::info('agente_retencionedit == on: ' . ($request->agente_retencionedit == 'on' ? 'true' : 'false'));

            // Usar el campo oculto como respaldo
            $agente_retencion_value = $request->agente_retencionedit == 'on' ? '1' : ($request->agente_retencionedit_hidden == '1' ? '1' : '0');
            $client->agente_retencion = $agente_retencion_value;
            $client->pasaporte = str_replace(['-', ' '], '', $request->pasaporteedit);
            $client->tipoContribuyente = $request->tipocontribuyenteedit;
            $client->economicactivity_id = $request->acteconomicaedit;
            $client->birthday = date('Ymd', strtotime($request->birthdayedit));
            $client->empresa = $request->empresaedit;
            $client->address_id = $address['id'];
            $client->phone_id = $phone['id'];
            $client->user_id_update = $id_user;
            $client->save();

            $com = $request->companyselectededit;

            // Devolver respuesta JSON para AJAX
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'redirect_url' => route('client.index', base64_encode($com))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //dd($id);
        $Client = Client::find(base64_decode($id));
        $Client->delete();
        return response()->json(array(
            "res" => "1"
        ));
    }

    public function getMovements($id)
    {
        $clientId = base64_decode($id);

        $movements = Sale::join('typedocuments', 'typedocuments.id', '=', 'sales.typedocument_id')
            ->leftJoin('dte', 'dte.sale_id', '=', 'sales.id')
            ->select(
                'sales.id',
                'sales.date',
                'sales.nu_doc',
                'sales.totalamount',
                'sales.state',
                'sales.typesale',
                'typedocuments.description as document_name',
                'dte.id_doc as dte_number',
                'dte.estadoHacienda'
            )
            ->where('sales.client_id', $clientId)
            ->orderBy('sales.date', 'desc')
            ->get();

        // ─── KPIs del cliente ─────────────────────────────────────────────────
        $completedSales = $movements->where('state', 1);
        $lastSale = $completedSales->first(); // Ya viene ordenado desc
        $oldestSale = $completedSales->last();

        $daysSinceLast = null;
        $purchaseFrequency = null;

        if ($lastSale) {
            $daysSinceLast = now()->diffInDays(\Carbon\Carbon::parse($lastSale->date));
        }

        if ($completedSales->count() > 1 && $lastSale && $oldestSale) {
            $totalDays = \Carbon\Carbon::parse($oldestSale->date)
                ->diffInDays(\Carbon\Carbon::parse($lastSale->date));
            $purchaseFrequency = $totalDays > 0
                ? round($totalDays / ($completedSales->count() - 1))
                : 0;
        }

        $avgTicket = $completedSales->count() > 0
            ? $completedSales->avg('totalamount')
            : 0;

        // ─── Productos por venta (para el desglose en el modal) ───────────────
        $saleIds = $movements->pluck('id');
        $productsBySale = DB::table('salesdetails')
            ->join('products', 'salesdetails.product_id', '=', 'products.id')
            ->select('salesdetails.sale_id', 'products.name as product_name', 'salesdetails.amountp as qty', 'salesdetails.pricesale')
            ->whereIn('salesdetails.sale_id', $saleIds)
            ->get()
            ->groupBy('sale_id');

        // ─── Tendencia mensual (últimos 12 meses) ─────────────────────────────
        $monthlyTrend = $completedSales
            ->groupBy(fn($s) => \Carbon\Carbon::parse($s->date)->format('Y-m'))
            ->map(fn($group) => [
                'month'  => \Carbon\Carbon::parse($group->first()->date)->translatedFormat('M Y'),
                'count'  => $group->count(),
                'amount' => round($group->sum('totalamount'), 2),
            ])
            ->values()
            ->sortBy(fn($v, $k) => $k)
            ->values()
            ->take(-12); // últimos 12 meses

        // ─── Adjuntar productos a cada movimiento ─────────────────────────────
        $movements = $movements->map(function ($mov) use ($productsBySale) {
            $mov->products = $productsBySale->get($mov->id, collect())->values();
            return $mov;
        });

        return response()->json([
            'movements'         => $movements,
            'kpis'              => [
                'total_movements'    => $movements->count(),
                'completed_sales'    => $completedSales->count(),
                'cancelled_sales'    => $movements->where('state', 0)->count(),
                'total_amount'       => round($completedSales->sum('totalamount'), 2),
                'avg_ticket'         => round($avgTicket, 2),
                'days_since_last'    => $daysSinceLast,
                'purchase_frequency' => $purchaseFrequency,
                'last_purchase_date' => $lastSale ? \Carbon\Carbon::parse($lastSale->date)->format('d/m/Y') : null,
            ],
            'monthly_trend'     => $monthlyTrend->values(),
        ]);
    }
}
