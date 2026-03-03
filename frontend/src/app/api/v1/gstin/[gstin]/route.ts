import { NextRequest, NextResponse } from "next/server";

const GSTIN_API_KEY = process.env.GSTIN_API_KEY || "";

const GSTIN_REGEX = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/;

interface GstinApiResponse {
  flag: boolean;
  message?: string;
  data?: {
    lgnm?: string;
    tradeNam?: string;
    sts?: string;
    dty?: string;
    ctb?: string;
    rgdt?: string;
    cxdt?: string;
    einvoiceStatus?: string;
    stjCd?: string;
    ctjCd?: string;
    nba?: string[];
    cmpRt?: string;
    pradr?: {
      adr?: string;
      addr?: {
        bno?: string;
        bnm?: string;
        flno?: string;
        st?: string;
        loc?: string;
        dst?: string;
        stcd?: string;
        pncd?: string;
        city?: string;
      };
    };
  };
}

export async function GET(
  _request: NextRequest,
  { params }: { params: Promise<{ gstin: string }> }
) {
  const { gstin } = await params;
  const normalized = gstin.trim().toUpperCase().replace(/\s+/g, "");

  if (!GSTIN_REGEX.test(normalized)) {
    return NextResponse.json(
      { success: false, message: "Invalid GSTIN format" },
      { status: 422 }
    );
  }

  if (!GSTIN_API_KEY) {
    return NextResponse.json(
      { success: false, message: "GSTIN API key not configured. Set GSTIN_API_KEY in .env.local" },
      { status: 500 }
    );
  }

  try {
    const res = await fetch(
      `https://sheet.gstincheck.co.in/check/${GSTIN_API_KEY}/${normalized}`,
      { next: { revalidate: 86400 } }
    );

    if (!res.ok) {
      return NextResponse.json(
        { success: false, message: "GSTIN lookup service unavailable" },
        { status: 502 }
      );
    }

    const json: GstinApiResponse = await res.json();

    if (!json.flag || !json.data) {
      return NextResponse.json(
        { success: false, message: json.message || "GSTIN not found" },
        { status: 404 }
      );
    }

    const d = json.data;
    const addr = d.pradr?.addr;

    return NextResponse.json({
      success: true,
      data: {
        gstin: normalized,
        legal_name: d.lgnm || null,
        trade_name: d.tradeNam || null,
        status: d.sts || null,
        type: d.dty || null,
        constitution: d.ctb || null,
        registration_date: d.rgdt || null,
        cancellation_date: d.cxdt || null,
        einvoice_status: d.einvoiceStatus || null,
        state_jurisdiction: d.stjCd || null,
        central_jurisdiction: d.ctjCd || null,
        nature_of_business: d.nba || [],
        compliance_rating: d.cmpRt || null,
        address: addr
          ? {
              full: d.pradr?.adr || null,
              building_no: addr.bno || null,
              building_name: addr.bnm || null,
              floor_no: addr.flno || null,
              street: addr.st || null,
              location: addr.loc || null,
              district: addr.dst || null,
              state: addr.stcd || null,
              pincode: addr.pncd || null,
              city: addr.city || null,
            }
          : null,
      },
    });
  } catch {
    return NextResponse.json(
      { success: false, message: "Failed to reach GSTIN verification service" },
      { status: 502 }
    );
  }
}
