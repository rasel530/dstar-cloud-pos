--
-- PostgreSQL database dump
--

\restrict gd7uXE4ye9Bg1Ca8aWrb0wE9Rrr8YsvN6E9cz9N9cMoyZrYBtVEtLyXTo6EJGUn

-- Dumped from database version 15.18
-- Dumped by pg_dump version 15.18

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity_log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activity_log (
    id bigint NOT NULL,
    log_name character varying(255),
    description text NOT NULL,
    subject_type character varying(255),
    event character varying(255),
    subject_id uuid,
    causer_type character varying(255),
    causer_id uuid,
    properties jsonb,
    batch_uuid uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.activity_log_id_seq OWNED BY public.activity_log.id;


--
-- Name: application_properties; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_properties (
    name character varying(255) NOT NULL,
    tenant_id uuid,
    value text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: application_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.application_settings (
    id uuid NOT NULL,
    tenant_id uuid,
    key character varying(255) NOT NULL,
    value jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: barcodes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.barcodes (
    id uuid NOT NULL,
    product_id uuid NOT NULL,
    value character varying(255) NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    barcode_type character varying(20) DEFAULT 'CODE_128'::character varying NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL
);


--
-- Name: branch_inventories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.branch_inventories (
    id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    product_id uuid NOT NULL,
    branch_id uuid NOT NULL,
    stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    reserved_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    minimum_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    maximum_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    last_purchase_price numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    selling_price numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    version integer DEFAULT 0 NOT NULL
);


--
-- Name: branch_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.branch_settings (
    id uuid NOT NULL,
    branch_id uuid NOT NULL,
    key character varying(100) NOT NULL,
    value jsonb,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: companies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.companies (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    address text,
    street_name character varying(255),
    additional_street_name character varying(255),
    building_number character varying(50),
    plot_identification character varying(100),
    city_subdivision_name character varying(100),
    city character varying(100),
    postal_code character varying(20),
    country_id uuid,
    country_subentity character varying(100),
    tax_number character varying(100),
    email character varying(255),
    phone_number character varying(50),
    bank_account_number character varying(100),
    bank_details text,
    logo bytea,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: counters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.counters (
    tenant_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    value integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: countries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.countries (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(10),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: currencies; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.currencies (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    code character varying(10),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: customer_discounts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.customer_discounts (
    id uuid NOT NULL,
    customer_id uuid NOT NULL,
    type smallint DEFAULT '0'::smallint NOT NULL,
    uid character varying(255),
    value numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: customers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.customers (
    id uuid NOT NULL,
    tenant_id uuid,
    code character varying(100),
    name character varying(255) NOT NULL,
    tax_number character varying(100),
    email character varying(255),
    phone_number character varying(50),
    address text,
    street_name character varying(255),
    additional_street_name character varying(255),
    building_number character varying(50),
    plot_identification character varying(100),
    city_subdivision_name character varying(100),
    city character varying(100),
    postal_code character varying(20),
    country_id uuid,
    country_subentity character varying(100),
    is_tax_exempt boolean DEFAULT false NOT NULL,
    is_supplier boolean DEFAULT false NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    due_date_period integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    price_list_id uuid
);


--
-- Name: document_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_categories (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    language_key character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: document_item_expiration_dates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_item_expiration_dates (
    document_item_id uuid NOT NULL,
    expiration_date date NOT NULL
);


--
-- Name: document_item_taxes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_item_taxes (
    document_item_id uuid NOT NULL,
    tax_id uuid NOT NULL,
    amount numeric(12,4) DEFAULT '0'::numeric NOT NULL
);


--
-- Name: document_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_items (
    id uuid NOT NULL,
    document_id uuid NOT NULL,
    quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    expected_quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    price numeric(12,4) NOT NULL,
    price_before_tax numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    price_before_tax_after_discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    price_after_discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    discount_apply_rule smallint DEFAULT '0'::smallint NOT NULL,
    product_cost numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_after_document_discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    product_id uuid NOT NULL
);


--
-- Name: document_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.document_types (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    code character varying(50) NOT NULL,
    document_category_id uuid NOT NULL,
    warehouse_id uuid,
    stock_direction smallint DEFAULT '0'::smallint NOT NULL,
    editor_type smallint DEFAULT '0'::smallint NOT NULL,
    print_template text,
    price_type smallint DEFAULT '0'::smallint NOT NULL,
    language_key character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: documents; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.documents (
    id uuid NOT NULL,
    number character varying(100) NOT NULL,
    order_number character varying(255),
    date date NOT NULL,
    stock_date timestamp(0) with time zone NOT NULL,
    total numeric(12,4) NOT NULL,
    discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    discount_apply_rule smallint DEFAULT '0'::smallint NOT NULL,
    is_clocked_out boolean DEFAULT false NOT NULL,
    reference_document_number character varying(100),
    internal_note text,
    note text,
    due_date date,
    paid_status smallint DEFAULT '0'::smallint NOT NULL,
    service_type smallint DEFAULT '0'::smallint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tenant_id uuid,
    user_id uuid NOT NULL,
    customer_id uuid,
    document_type_id uuid NOT NULL,
    warehouse_id uuid NOT NULL
);


--
-- Name: fiscal_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.fiscal_items (
    plu integer NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    vat character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: floor_plan_tables; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.floor_plan_tables (
    id uuid NOT NULL,
    floor_plan_id uuid NOT NULL,
    name character varying(255) NOT NULL,
    position_x double precision DEFAULT '0'::double precision NOT NULL,
    position_y double precision DEFAULT '0'::double precision NOT NULL,
    width double precision NOT NULL,
    height double precision NOT NULL,
    is_round boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: floor_plans; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.floor_plans (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    color character varying(50) DEFAULT 'Transparent'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tenant_id uuid
);


--
-- Name: income_expense_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.income_expense_categories (
    id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    name character varying(100) NOT NULL,
    type character varying(10) NOT NULL,
    color character varying(7) DEFAULT '#6b7280'::character varying NOT NULL,
    icon character varying(50),
    rank integer DEFAULT 0 NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: income_expenses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.income_expenses (
    id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    branch_id uuid,
    category_id uuid NOT NULL,
    user_id uuid NOT NULL,
    document_id uuid,
    reference_number character varying(50) NOT NULL,
    type character varying(10) NOT NULL,
    amount numeric(14,4) NOT NULL,
    description text,
    payment_method character varying(50),
    date date NOT NULL,
    status character varying(20) DEFAULT 'completed'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: loyalty_cards; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.loyalty_cards (
    id uuid NOT NULL,
    tenant_id uuid,
    customer_id uuid NOT NULL,
    card_number character varying(255),
    points_balance integer DEFAULT 0 NOT NULL,
    total_points_earned integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: loyalty_transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.loyalty_transactions (
    id uuid NOT NULL,
    loyalty_card_id uuid NOT NULL,
    points integer NOT NULL,
    transaction_type character varying(20) NOT NULL,
    reference_type character varying(50),
    reference_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: payment_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.payment_types (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    code character varying(50),
    is_customer_required boolean DEFAULT false NOT NULL,
    is_fiscal boolean DEFAULT true NOT NULL,
    is_slip_required boolean DEFAULT false NOT NULL,
    is_change_allowed boolean DEFAULT true NOT NULL,
    is_quick_payment boolean DEFAULT true NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    open_cash_drawer boolean DEFAULT true NOT NULL,
    shortcut_key character varying(10),
    mark_as_paid boolean DEFAULT true NOT NULL,
    rounding_increment numeric(10,4) DEFAULT '0'::numeric NOT NULL,
    rounding_rule smallint DEFAULT '0'::smallint NOT NULL,
    ordinal integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: payments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.payments (
    id uuid NOT NULL,
    tenant_id uuid,
    document_id uuid NOT NULL,
    payment_type_id uuid NOT NULL,
    user_id uuid NOT NULL,
    z_report_id uuid,
    amount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    rounding_adjustment numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    date date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id uuid NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: pos_order_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_order_items (
    id uuid NOT NULL,
    pos_order_id uuid NOT NULL,
    product_id uuid NOT NULL,
    round_number integer DEFAULT 0 NOT NULL,
    quantity numeric(12,4) NOT NULL,
    price numeric(12,4) NOT NULL,
    discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    discount_applied_type smallint DEFAULT '0'::smallint NOT NULL,
    is_locked boolean DEFAULT false NOT NULL,
    is_featured boolean DEFAULT false NOT NULL,
    voided_by uuid,
    comment text,
    bundle text,
    date_created date DEFAULT '2026-08-03'::date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pos_orders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_orders (
    id uuid NOT NULL,
    tenant_id uuid,
    user_id uuid NOT NULL,
    customer_id uuid,
    number character varying(50) NOT NULL,
    discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    total numeric(12,4),
    service_type smallint DEFAULT '0'::smallint NOT NULL,
    status character varying(20) DEFAULT 'open'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    loyalty_points_earned integer DEFAULT 0 NOT NULL,
    branch_id uuid,
    paid_amount numeric(12,4),
    change_amount numeric(12,4),
    payment_method character varying(50),
    tax_amount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    table_number character varying(50)
);


--
-- Name: pos_printer_selection_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_printer_selection_settings (
    id uuid NOT NULL,
    pos_printer_selection_id uuid NOT NULL,
    paper_width integer DEFAULT 32 NOT NULL,
    header text,
    footer text,
    feed_lines integer DEFAULT 0 NOT NULL,
    cut_paper boolean DEFAULT true NOT NULL,
    print_bitmap boolean DEFAULT false NOT NULL,
    open_cash_drawer boolean DEFAULT true NOT NULL,
    cash_drawer_command text,
    header_alignment smallint DEFAULT '0'::smallint NOT NULL,
    footer_alignment smallint DEFAULT '0'::smallint NOT NULL,
    is_formatting_enabled boolean DEFAULT true NOT NULL,
    printer_type smallint DEFAULT '0'::smallint NOT NULL,
    number_of_copies integer DEFAULT 1 NOT NULL,
    code_page integer DEFAULT '-1'::integer NOT NULL,
    character_set integer DEFAULT '-1'::integer NOT NULL,
    margin integer DEFAULT 0 NOT NULL,
    left_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    top_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    right_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    bottom_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    print_barcode boolean DEFAULT true NOT NULL,
    font_name character varying(255),
    font_size_percent numeric(8,2) DEFAULT '100'::numeric NOT NULL,
    print_logo_full_width boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pos_printer_selections; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_printer_selections (
    id uuid NOT NULL,
    key character varying(255) NOT NULL,
    printer_name character varying(255),
    is_enabled boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tenant_id uuid
);


--
-- Name: pos_printer_settings; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_printer_settings (
    id uuid NOT NULL,
    tenant_id uuid,
    printer_name character varying(255) NOT NULL,
    paper_width integer DEFAULT 32 NOT NULL,
    header text,
    footer text,
    feed_lines integer DEFAULT 0 NOT NULL,
    cut_paper boolean DEFAULT true NOT NULL,
    print_bitmap boolean DEFAULT false NOT NULL,
    open_cash_drawer boolean DEFAULT true NOT NULL,
    cash_drawer_command text,
    header_alignment smallint DEFAULT '0'::smallint NOT NULL,
    footer_alignment smallint DEFAULT '0'::smallint NOT NULL,
    is_formatting_enabled boolean DEFAULT true NOT NULL,
    printer_type smallint DEFAULT '0'::smallint NOT NULL,
    number_of_copies integer DEFAULT 1 NOT NULL,
    code_page integer DEFAULT '-1'::integer NOT NULL,
    character_set integer DEFAULT '-1'::integer NOT NULL,
    margin integer DEFAULT 0 NOT NULL,
    left_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    top_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    right_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    bottom_margin numeric(8,2) DEFAULT '0'::numeric NOT NULL,
    print_barcode boolean DEFAULT true NOT NULL,
    font_name character varying(255),
    font_size_percent numeric(8,2) DEFAULT '100'::numeric NOT NULL,
    print_logo_full_width boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pos_voids; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pos_voids (
    id uuid NOT NULL,
    tenant_id uuid,
    order_number character varying(255) NOT NULL,
    user_id uuid,
    user_name character varying(255) NOT NULL,
    product_id uuid,
    product_name character varying(255) NOT NULL,
    round_number integer NOT NULL,
    quantity numeric(12,4) NOT NULL,
    price numeric(12,4) NOT NULL,
    discount numeric(12,4) NOT NULL,
    discount_type smallint NOT NULL,
    total numeric(12,4) NOT NULL,
    is_confirmed boolean NOT NULL,
    reason text,
    voided_by uuid,
    voided_by_name character varying(255),
    bundle text,
    date_created timestamp(0) with time zone NOT NULL,
    date_voided timestamp(0) with time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: price_list_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.price_list_items (
    id uuid NOT NULL,
    price_list_id uuid NOT NULL,
    product_id uuid NOT NULL,
    price numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    markup numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: price_lists; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.price_lists (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    are_promotions_allowed boolean DEFAULT true NOT NULL,
    is_active boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: product_comments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_comments (
    id uuid NOT NULL,
    product_id uuid NOT NULL,
    comment text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: product_groups; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_groups (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    parent_group_id uuid,
    color character varying(50) DEFAULT 'Transparent'::character varying NOT NULL,
    image bytea,
    rank integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: product_taxes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_taxes (
    product_id uuid NOT NULL,
    tax_id uuid NOT NULL
);


--
-- Name: products; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.products (
    id uuid NOT NULL,
    tenant_id uuid,
    product_group_id uuid,
    name character varying(255) NOT NULL,
    code character varying(100),
    plu integer,
    measurement_unit character varying(50),
    price numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    cost numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    markup numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    last_purchase_price numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    is_tax_inclusive_price boolean DEFAULT true NOT NULL,
    is_price_change_allowed boolean DEFAULT false NOT NULL,
    is_service boolean DEFAULT false NOT NULL,
    is_using_default_quantity boolean DEFAULT true NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    description text,
    image bytea,
    color character varying(50) DEFAULT 'Transparent'::character varying NOT NULL,
    age_restriction integer,
    rank integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    mrp numeric(12,4),
    track_inventory boolean DEFAULT true NOT NULL,
    is_global boolean DEFAULT true NOT NULL,
    preferred_supplier_id uuid
);


--
-- Name: promotion_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.promotion_items (
    id uuid NOT NULL,
    promotion_id uuid NOT NULL,
    uid character varying(255) NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    price_type smallint DEFAULT '0'::smallint NOT NULL,
    value numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    is_conditional boolean DEFAULT true NOT NULL,
    quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    condition_type smallint DEFAULT '0'::smallint NOT NULL,
    quantity_limit numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: promotions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.promotions (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    start_date date,
    start_time time(0) without time zone,
    end_date date,
    end_time time(0) without time zone,
    days_of_week integer DEFAULT 127 NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: purchase_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.purchase_items (
    id uuid NOT NULL,
    purchase_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity numeric(14,4) NOT NULL,
    received_quantity numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    unit_cost numeric(14,4) NOT NULL,
    tax_id uuid,
    tax_amount numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    discount numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    total numeric(14,4) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: purchase_return_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.purchase_return_items (
    id uuid NOT NULL,
    return_id uuid NOT NULL,
    purchase_item_id uuid,
    product_id uuid NOT NULL,
    quantity numeric(14,4) NOT NULL,
    unit_cost numeric(14,4) NOT NULL,
    total numeric(14,4) NOT NULL,
    reason text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: purchase_returns; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.purchase_returns (
    id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    purchase_id uuid NOT NULL,
    supplier_id uuid,
    warehouse_id uuid,
    return_number character varying(50) NOT NULL,
    return_date timestamp(0) with time zone NOT NULL,
    subtotal numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    tax_total numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    grand_total numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    reason text,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    created_by uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: purchases; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.purchases (
    id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    supplier_id uuid,
    warehouse_id uuid,
    branch_id uuid,
    purchase_number character varying(50) NOT NULL,
    reference_number character varying(100),
    purchase_date timestamp(0) with time zone NOT NULL,
    expected_date timestamp(0) with time zone,
    received_date timestamp(0) with time zone,
    subtotal numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    discount numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    discount_type smallint DEFAULT '0'::smallint NOT NULL,
    tax_total numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    shipping_cost numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    grand_total numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    paid_amount numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    due_amount numeric(14,4) DEFAULT '0'::numeric NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    payment_status character varying(20) DEFAULT 'unpaid'::character varying NOT NULL,
    notes text,
    created_by uuid,
    received_by uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_permissions (
    id uuid NOT NULL,
    role_id uuid NOT NULL,
    module character varying(50) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(100) NOT NULL,
    access_level integer DEFAULT 0 NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: security_keys; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.security_keys (
    name character varying(255) NOT NULL,
    tenant_id uuid,
    level integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: starting_cash; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.starting_cash (
    id uuid NOT NULL,
    amount numeric(12,4) NOT NULL,
    description text,
    starting_cash_type smallint DEFAULT '0'::smallint NOT NULL,
    z_report_number integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tenant_id uuid,
    user_id uuid NOT NULL
);


--
-- Name: stock_controls; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stock_controls (
    id uuid NOT NULL,
    tenant_id uuid,
    product_id uuid NOT NULL,
    customer_id uuid,
    reorder_point numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    preferred_quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    is_low_stock_warning_enabled boolean DEFAULT true NOT NULL,
    low_stock_warning_quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    minimum_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    maximum_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    opening_stock numeric(12,4) DEFAULT '0'::numeric NOT NULL
);


--
-- Name: stock_movements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stock_movements (
    id uuid NOT NULL,
    tenant_id uuid,
    product_id uuid NOT NULL,
    warehouse_id uuid NOT NULL,
    movement_type character varying(50) NOT NULL,
    quantity_change numeric(12,4) NOT NULL,
    quantity_after numeric(12,4) NOT NULL,
    reference_type character varying(50),
    reference_id uuid,
    user_id uuid,
    note text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    purchase_id uuid
);


--
-- Name: stock_transfer_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stock_transfer_items (
    id uuid NOT NULL,
    transfer_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity numeric(12,4) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: stock_transfers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stock_transfers (
    id uuid NOT NULL,
    from_branch_id uuid NOT NULL,
    to_branch_id uuid NOT NULL,
    tenant_id uuid NOT NULL,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    notes text,
    created_by uuid NOT NULL,
    approved_by uuid,
    approved_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: stocks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stocks (
    id uuid NOT NULL,
    tenant_id uuid,
    product_id uuid NOT NULL,
    warehouse_id uuid NOT NULL,
    quantity numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    version integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: taxes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.taxes (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    rate numeric(8,4) NOT NULL,
    code character varying(50),
    is_fixed boolean DEFAULT false NOT NULL,
    is_tax_on_total boolean DEFAULT false NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: telescope_entries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.telescope_entries (
    sequence bigint NOT NULL,
    uuid uuid NOT NULL,
    batch_id uuid NOT NULL,
    family_hash character varying(255),
    should_display_on_index boolean DEFAULT true NOT NULL,
    type character varying(20) NOT NULL,
    content text NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: telescope_entries_sequence_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.telescope_entries_sequence_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: telescope_entries_sequence_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.telescope_entries_sequence_seq OWNED BY public.telescope_entries.sequence;


--
-- Name: telescope_entries_tags; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.telescope_entries_tags (
    entry_uuid uuid NOT NULL,
    tag character varying(255) NOT NULL
);


--
-- Name: telescope_monitoring; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.telescope_monitoring (
    tag character varying(255) NOT NULL
);


--
-- Name: templates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.templates (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    value text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: tenants; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tenants (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(100) NOT NULL,
    subdomain character varying(100),
    plan_type character varying(50) DEFAULT 'lite'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    trial_ends_at timestamp(0) with time zone,
    subscription_ends_at timestamp(0) with time zone,
    settings jsonb DEFAULT '{}'::jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    business_type character varying(50) DEFAULT 'retail'::character varying NOT NULL,
    branch_code character varying(50),
    address text,
    phone character varying(50),
    is_headquarters boolean DEFAULT false NOT NULL,
    company_id uuid,
    parent_branch_id uuid,
    is_company boolean DEFAULT false NOT NULL,
    country character varying(100),
    division character varying(100),
    district character varying(100),
    city character varying(100),
    latitude numeric(10,7),
    longitude numeric(10,7),
    manager_id uuid,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL
);


--
-- Name: user_activity_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_activity_logs (
    id uuid NOT NULL,
    user_id uuid,
    tenant_id uuid,
    module character varying(100),
    action character varying(100),
    url character varying(500),
    method character varying(10),
    ip_address character varying(45),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: user_branches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_branches (
    user_id uuid NOT NULL,
    branch_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id uuid NOT NULL,
    tenant_id uuid,
    first_name character varying(100),
    last_name character varying(100),
    username character varying(100),
    email character varying(255),
    password character varying(255) NOT NULL,
    access_level integer DEFAULT 0 NOT NULL,
    is_enabled boolean DEFAULT true NOT NULL,
    pin_code character varying(255),
    last_login_at timestamp(0) with time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    branch_id uuid,
    pin_attempts smallint DEFAULT '0'::smallint NOT NULL,
    pin_locked_until timestamp(0) with time zone,
    employee_number smallint
);


--
-- Name: void_reasons; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.void_reasons (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    rank integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: warehouses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.warehouses (
    id uuid NOT NULL,
    tenant_id uuid,
    name character varying(255) NOT NULL,
    is_default boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: z_reports; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.z_reports (
    id uuid NOT NULL,
    number integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    tenant_id uuid,
    from_document_id uuid NOT NULL,
    to_document_id uuid NOT NULL,
    user_id uuid,
    report_date date,
    period_from timestamp(0) without time zone,
    period_to timestamp(0) without time zone,
    starting_report_id uuid,
    total_sales integer DEFAULT 0 NOT NULL,
    gross_revenue numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_discount numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_tax numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_refunds numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    net_revenue numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_cash numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_card numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_digital_wallet numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    total_bank_transfer numeric(12,4) DEFAULT '0'::numeric NOT NULL,
    payment_breakdown json,
    closed_at timestamp(0) without time zone
);


--
-- Name: activity_log id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_log ALTER COLUMN id SET DEFAULT nextval('public.activity_log_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: telescope_entries sequence; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_entries ALTER COLUMN sequence SET DEFAULT nextval('public.telescope_entries_sequence_seq'::regclass);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (id);


--
-- Name: application_properties application_properties_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_properties
    ADD CONSTRAINT application_properties_pkey PRIMARY KEY (name);


--
-- Name: application_settings application_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_settings
    ADD CONSTRAINT application_settings_pkey PRIMARY KEY (id);


--
-- Name: application_settings application_settings_tenant_id_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_settings
    ADD CONSTRAINT application_settings_tenant_id_key_unique UNIQUE (tenant_id, key);


--
-- Name: barcodes barcodes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.barcodes
    ADD CONSTRAINT barcodes_pkey PRIMARY KEY (id);


--
-- Name: branch_inventories branch_inventories_branch_id_product_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_inventories
    ADD CONSTRAINT branch_inventories_branch_id_product_id_unique UNIQUE (branch_id, product_id);


--
-- Name: branch_inventories branch_inventories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_inventories
    ADD CONSTRAINT branch_inventories_pkey PRIMARY KEY (id);


--
-- Name: branch_settings branch_settings_branch_id_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_settings
    ADD CONSTRAINT branch_settings_branch_id_key_unique UNIQUE (branch_id, key);


--
-- Name: branch_settings branch_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_settings
    ADD CONSTRAINT branch_settings_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: counters counters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.counters
    ADD CONSTRAINT counters_pkey PRIMARY KEY (tenant_id, name);


--
-- Name: countries countries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.countries
    ADD CONSTRAINT countries_pkey PRIMARY KEY (id);


--
-- Name: currencies currencies_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.currencies
    ADD CONSTRAINT currencies_pkey PRIMARY KEY (id);


--
-- Name: customer_discounts customer_discounts_customer_id_type_uid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer_discounts
    ADD CONSTRAINT customer_discounts_customer_id_type_uid_unique UNIQUE (customer_id, type, uid);


--
-- Name: customer_discounts customer_discounts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer_discounts
    ADD CONSTRAINT customer_discounts_pkey PRIMARY KEY (id);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: document_categories document_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_categories
    ADD CONSTRAINT document_categories_pkey PRIMARY KEY (id);


--
-- Name: document_item_expiration_dates document_item_expiration_dates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_item_expiration_dates
    ADD CONSTRAINT document_item_expiration_dates_pkey PRIMARY KEY (document_item_id);


--
-- Name: document_item_taxes document_item_taxes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_item_taxes
    ADD CONSTRAINT document_item_taxes_pkey PRIMARY KEY (document_item_id, tax_id);


--
-- Name: document_items document_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_items
    ADD CONSTRAINT document_items_pkey PRIMARY KEY (id);


--
-- Name: document_types document_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_pkey PRIMARY KEY (id);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- Name: fiscal_items fiscal_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fiscal_items
    ADD CONSTRAINT fiscal_items_pkey PRIMARY KEY (plu);


--
-- Name: floor_plan_tables floor_plan_tables_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.floor_plan_tables
    ADD CONSTRAINT floor_plan_tables_pkey PRIMARY KEY (id);


--
-- Name: floor_plans floor_plans_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.floor_plans
    ADD CONSTRAINT floor_plans_pkey PRIMARY KEY (id);


--
-- Name: income_expense_categories income_expense_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expense_categories
    ADD CONSTRAINT income_expense_categories_pkey PRIMARY KEY (id);


--
-- Name: income_expenses income_expenses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_pkey PRIMARY KEY (id);


--
-- Name: income_expenses income_expenses_reference_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_reference_number_unique UNIQUE (reference_number);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: loyalty_cards loyalty_cards_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.loyalty_cards
    ADD CONSTRAINT loyalty_cards_pkey PRIMARY KEY (id);


--
-- Name: loyalty_transactions loyalty_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.loyalty_transactions
    ADD CONSTRAINT loyalty_transactions_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: payment_types payment_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_types
    ADD CONSTRAINT payment_types_pkey PRIMARY KEY (id);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: pos_order_items pos_order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_order_items
    ADD CONSTRAINT pos_order_items_pkey PRIMARY KEY (id);


--
-- Name: pos_orders pos_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_orders
    ADD CONSTRAINT pos_orders_pkey PRIMARY KEY (id);


--
-- Name: pos_printer_selection_settings pos_printer_selection_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_selection_settings
    ADD CONSTRAINT pos_printer_selection_settings_pkey PRIMARY KEY (id);


--
-- Name: pos_printer_selections pos_printer_selections_key_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_selections
    ADD CONSTRAINT pos_printer_selections_key_unique UNIQUE (key);


--
-- Name: pos_printer_selections pos_printer_selections_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_selections
    ADD CONSTRAINT pos_printer_selections_pkey PRIMARY KEY (id);


--
-- Name: pos_printer_settings pos_printer_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_settings
    ADD CONSTRAINT pos_printer_settings_pkey PRIMARY KEY (id);


--
-- Name: pos_printer_settings pos_printer_settings_printer_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_settings
    ADD CONSTRAINT pos_printer_settings_printer_name_unique UNIQUE (printer_name);


--
-- Name: pos_voids pos_voids_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_voids
    ADD CONSTRAINT pos_voids_pkey PRIMARY KEY (id);


--
-- Name: price_list_items price_list_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.price_list_items
    ADD CONSTRAINT price_list_items_pkey PRIMARY KEY (id);


--
-- Name: price_lists price_lists_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.price_lists
    ADD CONSTRAINT price_lists_pkey PRIMARY KEY (id);


--
-- Name: product_comments product_comments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_comments
    ADD CONSTRAINT product_comments_pkey PRIMARY KEY (id);


--
-- Name: product_groups product_groups_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_groups
    ADD CONSTRAINT product_groups_pkey PRIMARY KEY (id);


--
-- Name: product_taxes product_taxes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_taxes
    ADD CONSTRAINT product_taxes_pkey PRIMARY KEY (product_id, tax_id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: products products_plu_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_plu_unique UNIQUE (plu);


--
-- Name: promotion_items promotion_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promotion_items
    ADD CONSTRAINT promotion_items_pkey PRIMARY KEY (id);


--
-- Name: promotions promotions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promotions
    ADD CONSTRAINT promotions_pkey PRIMARY KEY (id);


--
-- Name: purchase_items purchase_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_items
    ADD CONSTRAINT purchase_items_pkey PRIMARY KEY (id);


--
-- Name: purchase_return_items purchase_return_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_return_items
    ADD CONSTRAINT purchase_return_items_pkey PRIMARY KEY (id);


--
-- Name: purchase_returns purchase_returns_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_pkey PRIMARY KEY (id);


--
-- Name: purchase_returns purchase_returns_return_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_return_number_unique UNIQUE (return_number);


--
-- Name: purchases purchases_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_pkey PRIMARY KEY (id);


--
-- Name: purchases purchases_purchase_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_purchase_number_unique UNIQUE (purchase_number);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (id);


--
-- Name: role_permissions role_permissions_role_id_module_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_module_unique UNIQUE (role_id, module);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: security_keys security_keys_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_keys
    ADD CONSTRAINT security_keys_pkey PRIMARY KEY (name);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: starting_cash starting_cash_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.starting_cash
    ADD CONSTRAINT starting_cash_pkey PRIMARY KEY (id);


--
-- Name: stock_controls stock_controls_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_controls
    ADD CONSTRAINT stock_controls_pkey PRIMARY KEY (id);


--
-- Name: stock_movements stock_movements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_pkey PRIMARY KEY (id);


--
-- Name: stock_transfer_items stock_transfer_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfer_items
    ADD CONSTRAINT stock_transfer_items_pkey PRIMARY KEY (id);


--
-- Name: stock_transfers stock_transfers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_pkey PRIMARY KEY (id);


--
-- Name: stocks stocks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stocks
    ADD CONSTRAINT stocks_pkey PRIMARY KEY (id);


--
-- Name: stocks stocks_product_id_warehouse_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stocks
    ADD CONSTRAINT stocks_product_id_warehouse_id_unique UNIQUE (product_id, warehouse_id);


--
-- Name: taxes taxes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_pkey PRIMARY KEY (id);


--
-- Name: telescope_entries telescope_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_entries
    ADD CONSTRAINT telescope_entries_pkey PRIMARY KEY (sequence);


--
-- Name: telescope_entries_tags telescope_entries_tags_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_entries_tags
    ADD CONSTRAINT telescope_entries_tags_pkey PRIMARY KEY (entry_uuid, tag);


--
-- Name: telescope_entries telescope_entries_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_entries
    ADD CONSTRAINT telescope_entries_uuid_unique UNIQUE (uuid);


--
-- Name: telescope_monitoring telescope_monitoring_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_monitoring
    ADD CONSTRAINT telescope_monitoring_pkey PRIMARY KEY (tag);


--
-- Name: templates templates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.templates
    ADD CONSTRAINT templates_pkey PRIMARY KEY (id);


--
-- Name: tenants tenants_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_pkey PRIMARY KEY (id);


--
-- Name: tenants tenants_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_slug_unique UNIQUE (slug);


--
-- Name: tenants tenants_subdomain_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_subdomain_unique UNIQUE (subdomain);


--
-- Name: user_activity_logs user_activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_pkey PRIMARY KEY (id);


--
-- Name: user_branches user_branches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_branches
    ADD CONSTRAINT user_branches_pkey PRIMARY KEY (user_id, branch_id);


--
-- Name: user_branches user_branches_user_id_branch_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_branches
    ADD CONSTRAINT user_branches_user_id_branch_id_unique UNIQUE (user_id, branch_id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_tenant_id_employee_number_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_tenant_id_employee_number_unique UNIQUE (tenant_id, employee_number);


--
-- Name: users users_username_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_unique UNIQUE (username);


--
-- Name: void_reasons void_reasons_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.void_reasons
    ADD CONSTRAINT void_reasons_pkey PRIMARY KEY (id);


--
-- Name: warehouses warehouses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_pkey PRIMARY KEY (id);


--
-- Name: z_reports z_reports_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.z_reports
    ADD CONSTRAINT z_reports_pkey PRIMARY KEY (id);


--
-- Name: activity_log_causer_type_causer_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_log_causer_type_causer_id_index ON public.activity_log USING btree (causer_type, causer_id);


--
-- Name: activity_log_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_log_created_at_index ON public.activity_log USING btree (created_at);


--
-- Name: activity_log_log_name_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_log_log_name_index ON public.activity_log USING btree (log_name);


--
-- Name: activity_log_subject_type_subject_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activity_log_subject_type_subject_id_index ON public.activity_log USING btree (subject_type, subject_id);


--
-- Name: application_settings_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX application_settings_tenant_id_index ON public.application_settings USING btree (tenant_id);


--
-- Name: barcodes_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX barcodes_product_id_index ON public.barcodes USING btree (product_id);


--
-- Name: barcodes_value_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX barcodes_value_index ON public.barcodes USING btree (value);


--
-- Name: branch_inventories_branch_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX branch_inventories_branch_id_index ON public.branch_inventories USING btree (branch_id);


--
-- Name: branch_inventories_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX branch_inventories_product_id_index ON public.branch_inventories USING btree (product_id);


--
-- Name: branch_inventories_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX branch_inventories_tenant_id_index ON public.branch_inventories USING btree (tenant_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: companies_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX companies_tenant_id_index ON public.companies USING btree (tenant_id);


--
-- Name: customer_discounts_customer_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customer_discounts_customer_id_index ON public.customer_discounts USING btree (customer_id);


--
-- Name: customers_code_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customers_code_index ON public.customers USING btree (code);


--
-- Name: customers_phone_number_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customers_phone_number_index ON public.customers USING btree (phone_number);


--
-- Name: customers_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customers_tenant_id_index ON public.customers USING btree (tenant_id);


--
-- Name: document_categories_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_categories_tenant_id_index ON public.document_categories USING btree (tenant_id);


--
-- Name: document_item_taxes_document_item_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_item_taxes_document_item_id_index ON public.document_item_taxes USING btree (document_item_id);


--
-- Name: document_item_taxes_tax_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_item_taxes_tax_id_index ON public.document_item_taxes USING btree (tax_id);


--
-- Name: document_items_document_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_items_document_id_index ON public.document_items USING btree (document_id);


--
-- Name: document_items_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_items_product_id_index ON public.document_items USING btree (product_id);


--
-- Name: document_types_document_category_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_types_document_category_id_index ON public.document_types USING btree (document_category_id);


--
-- Name: document_types_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX document_types_tenant_id_index ON public.document_types USING btree (tenant_id);


--
-- Name: documents_customer_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_customer_id_index ON public.documents USING btree (customer_id);


--
-- Name: documents_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_date_index ON public.documents USING btree (date);


--
-- Name: documents_document_type_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_document_type_id_index ON public.documents USING btree (document_type_id);


--
-- Name: documents_number_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_number_index ON public.documents USING btree (number);


--
-- Name: documents_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_tenant_id_index ON public.documents USING btree (tenant_id);


--
-- Name: documents_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX documents_user_id_index ON public.documents USING btree (user_id);


--
-- Name: floor_plan_tables_floor_plan_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX floor_plan_tables_floor_plan_id_index ON public.floor_plan_tables USING btree (floor_plan_id);


--
-- Name: idx_customers_name; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_customers_name ON public.customers USING gin (to_tsvector('english'::regconfig, (name)::text));


--
-- Name: idx_products_name; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_products_name ON public.products USING gin (to_tsvector('english'::regconfig, (name)::text));


--
-- Name: income_expense_categories_tenant_id_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX income_expense_categories_tenant_id_type_index ON public.income_expense_categories USING btree (tenant_id, type);


--
-- Name: income_expenses_category_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX income_expenses_category_id_index ON public.income_expenses USING btree (category_id);


--
-- Name: income_expenses_tenant_id_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX income_expenses_tenant_id_date_index ON public.income_expenses USING btree (tenant_id, date);


--
-- Name: income_expenses_tenant_id_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX income_expenses_tenant_id_type_index ON public.income_expenses USING btree (tenant_id, type);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: loyalty_cards_customer_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX loyalty_cards_customer_id_index ON public.loyalty_cards USING btree (customer_id);


--
-- Name: loyalty_cards_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX loyalty_cards_tenant_id_index ON public.loyalty_cards USING btree (tenant_id);


--
-- Name: loyalty_transactions_loyalty_card_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX loyalty_transactions_loyalty_card_id_index ON public.loyalty_transactions USING btree (loyalty_card_id);


--
-- Name: payment_types_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payment_types_tenant_id_index ON public.payment_types USING btree (tenant_id);


--
-- Name: payments_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payments_date_index ON public.payments USING btree (date);


--
-- Name: payments_document_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payments_document_id_index ON public.payments USING btree (document_id);


--
-- Name: payments_payment_type_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payments_payment_type_id_index ON public.payments USING btree (payment_type_id);


--
-- Name: payments_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX payments_tenant_id_index ON public.payments USING btree (tenant_id);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: pos_order_items_pos_order_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_order_items_pos_order_id_index ON public.pos_order_items USING btree (pos_order_id);


--
-- Name: pos_order_items_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_order_items_product_id_index ON public.pos_order_items USING btree (product_id);


--
-- Name: pos_orders_branch_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_orders_branch_id_index ON public.pos_orders USING btree (branch_id);


--
-- Name: pos_orders_number_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_orders_number_index ON public.pos_orders USING btree (number);


--
-- Name: pos_orders_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_orders_tenant_id_index ON public.pos_orders USING btree (tenant_id);


--
-- Name: pos_orders_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_orders_user_id_index ON public.pos_orders USING btree (user_id);


--
-- Name: pos_printer_selection_settings_pos_printer_selection_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_printer_selection_settings_pos_printer_selection_id_index ON public.pos_printer_selection_settings USING btree (pos_printer_selection_id);


--
-- Name: pos_printer_selections_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_printer_selections_tenant_id_index ON public.pos_printer_selections USING btree (tenant_id);


--
-- Name: pos_printer_settings_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_printer_settings_tenant_id_index ON public.pos_printer_settings USING btree (tenant_id);


--
-- Name: pos_voids_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pos_voids_tenant_id_index ON public.pos_voids USING btree (tenant_id);


--
-- Name: price_list_items_price_list_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX price_list_items_price_list_id_index ON public.price_list_items USING btree (price_list_id);


--
-- Name: price_list_items_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX price_list_items_product_id_index ON public.price_list_items USING btree (product_id);


--
-- Name: price_lists_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX price_lists_tenant_id_index ON public.price_lists USING btree (tenant_id);


--
-- Name: product_groups_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_groups_tenant_id_index ON public.product_groups USING btree (tenant_id);


--
-- Name: products_code_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_code_index ON public.products USING btree (code);


--
-- Name: products_plu_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_plu_index ON public.products USING btree (plu);


--
-- Name: products_product_group_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_product_group_id_index ON public.products USING btree (product_group_id);


--
-- Name: products_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_tenant_id_index ON public.products USING btree (tenant_id);


--
-- Name: products_tenant_id_is_enabled_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_tenant_id_is_enabled_index ON public.products USING btree (tenant_id, is_enabled);


--
-- Name: promotion_items_promotion_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX promotion_items_promotion_id_index ON public.promotion_items USING btree (promotion_id);


--
-- Name: promotions_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX promotions_tenant_id_index ON public.promotions USING btree (tenant_id);


--
-- Name: purchases_purchase_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX purchases_purchase_date_index ON public.purchases USING btree (purchase_date);


--
-- Name: purchases_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX purchases_status_index ON public.purchases USING btree (status);


--
-- Name: purchases_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX purchases_tenant_id_index ON public.purchases USING btree (tenant_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: starting_cash_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX starting_cash_tenant_id_index ON public.starting_cash USING btree (tenant_id);


--
-- Name: stock_controls_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_controls_tenant_id_index ON public.stock_controls USING btree (tenant_id);


--
-- Name: stock_movements_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_movements_created_at_index ON public.stock_movements USING btree (created_at);


--
-- Name: stock_movements_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_movements_product_id_index ON public.stock_movements USING btree (product_id);


--
-- Name: stock_movements_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_movements_tenant_id_index ON public.stock_movements USING btree (tenant_id);


--
-- Name: stocks_product_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stocks_product_id_index ON public.stocks USING btree (product_id);


--
-- Name: stocks_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stocks_tenant_id_index ON public.stocks USING btree (tenant_id);


--
-- Name: stocks_warehouse_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stocks_warehouse_id_index ON public.stocks USING btree (warehouse_id);


--
-- Name: taxes_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX taxes_tenant_id_index ON public.taxes USING btree (tenant_id);


--
-- Name: telescope_entries_batch_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX telescope_entries_batch_id_index ON public.telescope_entries USING btree (batch_id);


--
-- Name: telescope_entries_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX telescope_entries_created_at_index ON public.telescope_entries USING btree (created_at);


--
-- Name: telescope_entries_family_hash_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX telescope_entries_family_hash_index ON public.telescope_entries USING btree (family_hash);


--
-- Name: telescope_entries_tags_tag_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX telescope_entries_tags_tag_index ON public.telescope_entries_tags USING btree (tag);


--
-- Name: telescope_entries_type_should_display_on_index_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX telescope_entries_type_should_display_on_index_index ON public.telescope_entries USING btree (type, should_display_on_index);


--
-- Name: templates_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX templates_tenant_id_index ON public.templates USING btree (tenant_id);


--
-- Name: user_activity_logs_module_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_activity_logs_module_created_at_index ON public.user_activity_logs USING btree (module, created_at);


--
-- Name: user_activity_logs_user_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_activity_logs_user_id_created_at_index ON public.user_activity_logs USING btree (user_id, created_at);


--
-- Name: users_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_email_index ON public.users USING btree (email);


--
-- Name: users_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_tenant_id_index ON public.users USING btree (tenant_id);


--
-- Name: users_username_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_username_index ON public.users USING btree (username);


--
-- Name: void_reasons_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX void_reasons_tenant_id_index ON public.void_reasons USING btree (tenant_id);


--
-- Name: warehouses_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX warehouses_tenant_id_index ON public.warehouses USING btree (tenant_id);


--
-- Name: z_reports_closed_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX z_reports_closed_at_index ON public.z_reports USING btree (closed_at);


--
-- Name: z_reports_number_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX z_reports_number_index ON public.z_reports USING btree (number);


--
-- Name: z_reports_report_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX z_reports_report_date_index ON public.z_reports USING btree (report_date);


--
-- Name: z_reports_tenant_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX z_reports_tenant_id_index ON public.z_reports USING btree (tenant_id);


--
-- Name: z_reports_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX z_reports_user_id_index ON public.z_reports USING btree (user_id);


--
-- Name: application_properties application_properties_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_properties
    ADD CONSTRAINT application_properties_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: application_settings application_settings_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.application_settings
    ADD CONSTRAINT application_settings_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: barcodes barcodes_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.barcodes
    ADD CONSTRAINT barcodes_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: branch_inventories branch_inventories_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_inventories
    ADD CONSTRAINT branch_inventories_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: branch_inventories branch_inventories_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_inventories
    ADD CONSTRAINT branch_inventories_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: branch_inventories branch_inventories_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_inventories
    ADD CONSTRAINT branch_inventories_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: branch_settings branch_settings_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branch_settings
    ADD CONSTRAINT branch_settings_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: companies companies_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id);


--
-- Name: companies companies_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: counters counters_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.counters
    ADD CONSTRAINT counters_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: customer_discounts customer_discounts_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customer_discounts
    ADD CONSTRAINT customer_discounts_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: customers customers_country_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_country_id_foreign FOREIGN KEY (country_id) REFERENCES public.countries(id);


--
-- Name: customers customers_price_list_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_price_list_id_foreign FOREIGN KEY (price_list_id) REFERENCES public.price_lists(id) ON DELETE SET NULL;


--
-- Name: customers customers_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: document_categories document_categories_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_categories
    ADD CONSTRAINT document_categories_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: document_item_expiration_dates document_item_expiration_dates_document_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_item_expiration_dates
    ADD CONSTRAINT document_item_expiration_dates_document_item_id_foreign FOREIGN KEY (document_item_id) REFERENCES public.document_items(id) ON DELETE CASCADE;


--
-- Name: document_item_taxes document_item_taxes_document_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_item_taxes
    ADD CONSTRAINT document_item_taxes_document_item_id_foreign FOREIGN KEY (document_item_id) REFERENCES public.document_items(id) ON DELETE CASCADE;


--
-- Name: document_item_taxes document_item_taxes_tax_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_item_taxes
    ADD CONSTRAINT document_item_taxes_tax_id_foreign FOREIGN KEY (tax_id) REFERENCES public.taxes(id) ON DELETE CASCADE;


--
-- Name: document_items document_items_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_items
    ADD CONSTRAINT document_items_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: document_items document_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_items
    ADD CONSTRAINT document_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: document_types document_types_document_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_document_category_id_foreign FOREIGN KEY (document_category_id) REFERENCES public.document_categories(id) ON DELETE CASCADE;


--
-- Name: document_types document_types_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: document_types document_types_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.document_types
    ADD CONSTRAINT document_types_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id) ON DELETE SET NULL;


--
-- Name: documents documents_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: documents documents_document_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_document_type_id_foreign FOREIGN KEY (document_type_id) REFERENCES public.document_types(id);


--
-- Name: documents documents_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: documents documents_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: documents documents_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id);


--
-- Name: fiscal_items fiscal_items_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.fiscal_items
    ADD CONSTRAINT fiscal_items_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: floor_plan_tables floor_plan_tables_floor_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.floor_plan_tables
    ADD CONSTRAINT floor_plan_tables_floor_plan_id_foreign FOREIGN KEY (floor_plan_id) REFERENCES public.floor_plans(id) ON DELETE CASCADE;


--
-- Name: floor_plans floor_plans_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.floor_plans
    ADD CONSTRAINT floor_plans_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: income_expense_categories income_expense_categories_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expense_categories
    ADD CONSTRAINT income_expense_categories_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: income_expenses income_expenses_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: income_expenses income_expenses_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.income_expense_categories(id);


--
-- Name: income_expenses income_expenses_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE SET NULL;


--
-- Name: income_expenses income_expenses_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: income_expenses income_expenses_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.income_expenses
    ADD CONSTRAINT income_expenses_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: loyalty_cards loyalty_cards_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.loyalty_cards
    ADD CONSTRAINT loyalty_cards_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: loyalty_cards loyalty_cards_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.loyalty_cards
    ADD CONSTRAINT loyalty_cards_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: loyalty_transactions loyalty_transactions_loyalty_card_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.loyalty_transactions
    ADD CONSTRAINT loyalty_transactions_loyalty_card_id_foreign FOREIGN KEY (loyalty_card_id) REFERENCES public.loyalty_cards(id) ON DELETE CASCADE;


--
-- Name: payment_types payment_types_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payment_types
    ADD CONSTRAINT payment_types_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: payments payments_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_document_id_foreign FOREIGN KEY (document_id) REFERENCES public.documents(id) ON DELETE CASCADE;


--
-- Name: payments payments_payment_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_payment_type_id_foreign FOREIGN KEY (payment_type_id) REFERENCES public.payment_types(id);


--
-- Name: payments payments_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: payments payments_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: payments payments_z_report_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_z_report_id_foreign FOREIGN KEY (z_report_id) REFERENCES public.z_reports(id) ON DELETE SET NULL;


--
-- Name: pos_order_items pos_order_items_pos_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_order_items
    ADD CONSTRAINT pos_order_items_pos_order_id_foreign FOREIGN KEY (pos_order_id) REFERENCES public.pos_orders(id) ON DELETE CASCADE;


--
-- Name: pos_order_items pos_order_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_order_items
    ADD CONSTRAINT pos_order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: pos_order_items pos_order_items_voided_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_order_items
    ADD CONSTRAINT pos_order_items_voided_by_foreign FOREIGN KEY (voided_by) REFERENCES public.users(id);


--
-- Name: pos_orders pos_orders_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_orders
    ADD CONSTRAINT pos_orders_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: pos_orders pos_orders_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_orders
    ADD CONSTRAINT pos_orders_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: pos_orders pos_orders_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_orders
    ADD CONSTRAINT pos_orders_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: pos_orders pos_orders_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_orders
    ADD CONSTRAINT pos_orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: pos_printer_selection_settings pos_printer_selection_settings_pos_printer_selection_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_selection_settings
    ADD CONSTRAINT pos_printer_selection_settings_pos_printer_selection_id_foreign FOREIGN KEY (pos_printer_selection_id) REFERENCES public.pos_printer_selections(id) ON DELETE CASCADE;


--
-- Name: pos_printer_selections pos_printer_selections_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_selections
    ADD CONSTRAINT pos_printer_selections_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: pos_printer_settings pos_printer_settings_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_printer_settings
    ADD CONSTRAINT pos_printer_settings_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: pos_voids pos_voids_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_voids
    ADD CONSTRAINT pos_voids_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE SET NULL;


--
-- Name: pos_voids pos_voids_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_voids
    ADD CONSTRAINT pos_voids_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: pos_voids pos_voids_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_voids
    ADD CONSTRAINT pos_voids_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: pos_voids pos_voids_voided_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pos_voids
    ADD CONSTRAINT pos_voids_voided_by_foreign FOREIGN KEY (voided_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: price_list_items price_list_items_price_list_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.price_list_items
    ADD CONSTRAINT price_list_items_price_list_id_foreign FOREIGN KEY (price_list_id) REFERENCES public.price_lists(id) ON DELETE CASCADE;


--
-- Name: price_list_items price_list_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.price_list_items
    ADD CONSTRAINT price_list_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: price_lists price_lists_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.price_lists
    ADD CONSTRAINT price_lists_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: product_comments product_comments_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_comments
    ADD CONSTRAINT product_comments_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_groups product_groups_parent_group_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_groups
    ADD CONSTRAINT product_groups_parent_group_id_foreign FOREIGN KEY (parent_group_id) REFERENCES public.product_groups(id) ON DELETE SET NULL;


--
-- Name: product_groups product_groups_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_groups
    ADD CONSTRAINT product_groups_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: product_taxes product_taxes_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_taxes
    ADD CONSTRAINT product_taxes_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_taxes product_taxes_tax_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_taxes
    ADD CONSTRAINT product_taxes_tax_id_foreign FOREIGN KEY (tax_id) REFERENCES public.taxes(id) ON DELETE CASCADE;


--
-- Name: products products_preferred_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_preferred_supplier_id_foreign FOREIGN KEY (preferred_supplier_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: products products_product_group_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_product_group_id_foreign FOREIGN KEY (product_group_id) REFERENCES public.product_groups(id) ON DELETE SET NULL;


--
-- Name: products products_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: promotion_items promotion_items_promotion_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promotion_items
    ADD CONSTRAINT promotion_items_promotion_id_foreign FOREIGN KEY (promotion_id) REFERENCES public.promotions(id) ON DELETE CASCADE;


--
-- Name: promotions promotions_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.promotions
    ADD CONSTRAINT promotions_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: purchase_items purchase_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_items
    ADD CONSTRAINT purchase_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: purchase_items purchase_items_purchase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_items
    ADD CONSTRAINT purchase_items_purchase_id_foreign FOREIGN KEY (purchase_id) REFERENCES public.purchases(id) ON DELETE CASCADE;


--
-- Name: purchase_items purchase_items_tax_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_items
    ADD CONSTRAINT purchase_items_tax_id_foreign FOREIGN KEY (tax_id) REFERENCES public.taxes(id) ON DELETE SET NULL;


--
-- Name: purchase_return_items purchase_return_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_return_items
    ADD CONSTRAINT purchase_return_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: purchase_return_items purchase_return_items_purchase_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_return_items
    ADD CONSTRAINT purchase_return_items_purchase_item_id_foreign FOREIGN KEY (purchase_item_id) REFERENCES public.purchase_items(id) ON DELETE SET NULL;


--
-- Name: purchase_return_items purchase_return_items_return_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_return_items
    ADD CONSTRAINT purchase_return_items_return_id_foreign FOREIGN KEY (return_id) REFERENCES public.purchase_returns(id) ON DELETE CASCADE;


--
-- Name: purchase_returns purchase_returns_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: purchase_returns purchase_returns_purchase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_purchase_id_foreign FOREIGN KEY (purchase_id) REFERENCES public.purchases(id) ON DELETE CASCADE;


--
-- Name: purchase_returns purchase_returns_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: purchase_returns purchase_returns_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: purchase_returns purchase_returns_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchase_returns
    ADD CONSTRAINT purchase_returns_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id) ON DELETE SET NULL;


--
-- Name: purchases purchases_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: purchases purchases_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: purchases purchases_received_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_received_by_foreign FOREIGN KEY (received_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: purchases purchases_supplier_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_supplier_id_foreign FOREIGN KEY (supplier_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: purchases purchases_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: purchases purchases_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.purchases
    ADD CONSTRAINT purchases_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id) ON DELETE SET NULL;


--
-- Name: role_permissions role_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: roles roles_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: security_keys security_keys_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.security_keys
    ADD CONSTRAINT security_keys_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: starting_cash starting_cash_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.starting_cash
    ADD CONSTRAINT starting_cash_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: starting_cash starting_cash_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.starting_cash
    ADD CONSTRAINT starting_cash_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: stock_controls stock_controls_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_controls
    ADD CONSTRAINT stock_controls_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: stock_controls stock_controls_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_controls
    ADD CONSTRAINT stock_controls_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: stock_controls stock_controls_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_controls
    ADD CONSTRAINT stock_controls_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: stock_movements stock_movements_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: stock_movements stock_movements_purchase_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_purchase_id_foreign FOREIGN KEY (purchase_id) REFERENCES public.purchases(id) ON DELETE SET NULL;


--
-- Name: stock_movements stock_movements_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: stock_movements stock_movements_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id);


--
-- Name: stock_movements stock_movements_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id) ON DELETE CASCADE;


--
-- Name: stock_transfer_items stock_transfer_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfer_items
    ADD CONSTRAINT stock_transfer_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id);


--
-- Name: stock_transfer_items stock_transfer_items_transfer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfer_items
    ADD CONSTRAINT stock_transfer_items_transfer_id_foreign FOREIGN KEY (transfer_id) REFERENCES public.stock_transfers(id) ON DELETE CASCADE;


--
-- Name: stock_transfers stock_transfers_approved_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES public.users(id);


--
-- Name: stock_transfers stock_transfers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id);


--
-- Name: stock_transfers stock_transfers_from_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_from_branch_id_foreign FOREIGN KEY (from_branch_id) REFERENCES public.tenants(id);


--
-- Name: stock_transfers stock_transfers_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id);


--
-- Name: stock_transfers stock_transfers_to_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_transfers
    ADD CONSTRAINT stock_transfers_to_branch_id_foreign FOREIGN KEY (to_branch_id) REFERENCES public.tenants(id);


--
-- Name: stocks stocks_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stocks
    ADD CONSTRAINT stocks_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: stocks stocks_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stocks
    ADD CONSTRAINT stocks_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: stocks stocks_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stocks
    ADD CONSTRAINT stocks_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(id) ON DELETE CASCADE;


--
-- Name: taxes taxes_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.taxes
    ADD CONSTRAINT taxes_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: telescope_entries_tags telescope_entries_tags_entry_uuid_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.telescope_entries_tags
    ADD CONSTRAINT telescope_entries_tags_entry_uuid_foreign FOREIGN KEY (entry_uuid) REFERENCES public.telescope_entries(uuid) ON DELETE CASCADE;


--
-- Name: templates templates_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.templates
    ADD CONSTRAINT templates_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: tenants tenants_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: tenants tenants_manager_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_manager_id_foreign FOREIGN KEY (manager_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: tenants tenants_parent_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_parent_branch_id_foreign FOREIGN KEY (parent_branch_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: user_activity_logs user_activity_logs_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: user_activity_logs user_activity_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_activity_logs
    ADD CONSTRAINT user_activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: user_branches user_branches_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_branches
    ADD CONSTRAINT user_branches_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: user_branches user_branches_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_branches
    ADD CONSTRAINT user_branches_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: users users_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.tenants(id) ON DELETE SET NULL;


--
-- Name: users users_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: void_reasons void_reasons_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.void_reasons
    ADD CONSTRAINT void_reasons_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: warehouses warehouses_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: z_reports z_reports_from_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.z_reports
    ADD CONSTRAINT z_reports_from_document_id_foreign FOREIGN KEY (from_document_id) REFERENCES public.documents(id);


--
-- Name: z_reports z_reports_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.z_reports
    ADD CONSTRAINT z_reports_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: z_reports z_reports_to_document_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.z_reports
    ADD CONSTRAINT z_reports_to_document_id_foreign FOREIGN KEY (to_document_id) REFERENCES public.documents(id);


--
-- Name: z_reports z_reports_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.z_reports
    ADD CONSTRAINT z_reports_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict gd7uXE4ye9Bg1Ca8aWrb0wE9Rrr8YsvN6E9cz9N9cMoyZrYBtVEtLyXTo6EJGUn

