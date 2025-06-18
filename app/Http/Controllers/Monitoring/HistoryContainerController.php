<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Monitoring\ContainerService;

class HistoryContainerController extends Controller
{
    protected $container;

    public function __construct(ContainerService $container)
    {
        $this->container = $container;
    }

    function index() {
        return view('history.container');
    }

    function container(Request $request) {
        $noContainer = $request->q;
        $data = $this->container->listContainerHistory($noContainer);
        return response()->json($data);
    }

    function getLocation(Request $request) {
        $noContainer = $request->no_cont;
        $data = $this->container->getLocation($noContainer);
        return $data->location ?? '';
    }

    function getStatusContainer(Request $request) {
        $noContainer = $request->no_cont;
        $noBooking = $request->no_booking;
        $data = $this->container->getStatusContainer($noContainer, $noBooking);
        return $data->status_cont ?? '';
    }

    function getBooking(Request $request) {
        $noContainer = $request->no_cont;
        $data = $this->container->getBooking($noContainer);
        return $data;
    }

    function ContainerVessel(Request $request) {
        $no_book = $request->no_book;
        return $this->container->ContainerVessel($no_book);
    }

    function getDetail(Request $request) {
        $no_cont = $request->input('NO_CONT');
        $act = $request->input('ACT');
        $counter = $request->input('COUNTER');
        $no_booking = $request->input('NO_BOOK');
        return $this->container->getDetail($no_cont,$act,$counter,$no_booking);
    }
    
}
