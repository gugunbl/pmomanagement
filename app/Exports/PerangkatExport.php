<?php

namespace App\Exports;

use App\Perangkat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerangkatExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = Perangkat::with(['project', 'lokasi', 'jenisPerangkat']);

        if (isset($this->request['search']) && !empty($this->request['search'])) {
            $search = $this->request['search'];
            $query->where(function ($q) use ($search) {
                $q->where('serialnumber', 'like', '%' . $search . '%')
                  ->orWhere('merk', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        if (isset($this->request['id_project']) && !empty($this->request['id_project'])) {
            $selectedProjects = array_values(array_filter((array) $this->request['id_project'], function ($idProject) {
                return $idProject !== null && $idProject !== '';
            }));

            if (count($selectedProjects) > 0) {
                $query->whereIn('id_project', $selectedProjects);
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Project',
            'Jenis Perangkat',
            'SIK',
            'Merk',
            'Type',
            'Serial Number',
            'Site',
            'Lokasi',
            'Alamat',
            'Koordinat',
            'Lantai',
            'Start Warranty',
            'End Warranty',
            'Tanggal Online'
        ];
    }

    public function map($perangkat): array
    {
        return [
            $perangkat->project->nama_project ?? '-',
            $perangkat->jenisPerangkat->jns_perangkat ?? '-',
            $perangkat->sik,
            $perangkat->merk,
            $perangkat->type,
            $perangkat->serialnumber,
            $perangkat->site,
            $perangkat->lokasi->nama_lokasi ?? '-',
            $perangkat->lokasi->alamat ?? '-',
            $perangkat->koordinat,
            $perangkat->lantai,
            $perangkat->start_warranty,
            $perangkat->end_warranty,
            $perangkat->tgl_online,
        ];
    }

    public function title(): string
    {
        return 'Data Perangkat';
    }
}
