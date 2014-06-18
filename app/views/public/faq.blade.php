@extends('public.header') 

@section('content')

<section class="hero background hero-faq" data-speed="2" data-type="background">
    <div class="container">
        <div class="row">
            <h1><span class="thin">THE</span> FAQs</h1>
        </div>
    </div>
</section>

<section class="faq">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="question">
                    <a class="expander" href="#">I know it isn’t standard
                        ninja practice to reveal too many identity details, but
                        who are you guys exactly?
                    </a>
                    <div class="content">
                        <p>We’re a small team of highly skilled digital
                            journeymen based in Israel. We love open source, we
                            love disrupting the big business status quo, and we
                            love building helpful tools that are easy to use.
                            We believe that everyone else’s web-based cash flow
                            tools are unnecessarily expensive, clunky and
                            complicated - and we’re bent on proving these
                            beliefs with Invoice Ninja.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">How do I get started using
                        Invoice Ninja?
                    </a>
                    <div class="content">
                        <p>Just click on the big, yellow “Invoice Now”
                            button on our homepage!
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">Do you offer customer
                        support?
                    </a>
                    <div class="content">
                        <p>We sure do. Support is super important to us.
                            Feel free to email us at <a href=
                            "mailto:support@invoiceninja.com">support@invoiceninja.com</a>
                            with any questions you might have. We almost always
                            reply within one business day.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">Is Invoice Ninja really
                        free? For how long?
                    </a>
                    <div class="content">
                        <p>Yes, our basic app is 100% free. Forever and ever. For real. We 
                            also offer a paid Pro version of Invoice Ninja (you can learn all about 
                            its awesome features <a href="https://www.invoiceninja.com/plans">here</a>), but it's 
                            important to us that the free version have all of the key features people 
                            need to do business.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">How is Invoice Ninja able
                        to offer this all for free? How are you making any
                        money?
                    </a>
                    <div class="content">
                        <p>We’re mostly in this line of work because we believe it’s high time that 
                            a good electronic invoicing tool be available for free. There isn’t much money 
                            in it - yet. We do offer a paid <a href="https://www.invoiceninja.com/plans">Pro </a> 
                            version of the app that we've souped up with premium features. And when our users open up new 
                            accounts with payment processor gateways by clicking on links from our site, we make 
                            modest commissions as a gateway affiliate. So if zillions of freelancers and small businesses 
                            start running credit card charges through Invoice Ninja, or if scores of users go
                            <a href="https://www.invoiceninja.com/plans">Pro</a>, there’s a decent chance we'll 
                            recover our investment.</p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">Really? So does that mean
                        you’re not collecting information about me so you can
                        sell me stuff or so that some other company can spam me
                        according to my interests?
                    </a>
                    <div class="content">
                        <p>No way. We’re not mining your data, and we’re
                            not selling you out. That wouldn’t be very ninja of
                            us, would it?
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">But don’t you have access
                        to my merchant and banking accounts
                    </a>
                    <div class="content">
                        <p>Actually, we don’t. When you link an account at
                            a third party financial institution with your
                            Invoice Ninja account, you’re essentially giving
                            our app permission to send money to you and nothing
                            more. This is all managed by the tech teams at your
                            financial service providers, who go to great
                            lengths to ensure their integrations can’t be
                            exploited or abused.
                        </p>
                    </div>
                 </div>
                <div class="question">
                    <a class="expander" href="#">Given that Invoice Ninja
                        is an open source app, how can I be sure that my
                        financial information is safe with you?
                    </a>
                    <div class="content">
                        <p>There’s a big difference between “open source”
                            and “open data.” Anyone who wants to use the code
                            that drives Invoice Ninja to create their own
                            products or to make improvements to ours can do so.
                            It’s available for anyone who wants to download and
                            work with. But that’s just the source code -
                            totally separate from what happens with that code
                            on the Invoice Ninja servers. You’re the only one
                            who has full access to what you're doing with our
                            product. For more details on the security of our
                            servers and how we handle our users’ information,
                            please read the next question.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">So just how secure is this
                        app?
                    </a>
                    <div class="content">
                            <p>Extremely. Data uploaded by our users runs
                            through connections with 256-bit encryption, which
                            is twice as many encryption bits that most bank
                            websites use. We use the TLS 1.0 cryptographic
                            protocol, AES_256_CBC string encryption, SHA1
                            message authentication and DHE_RSA key exchanges.
                            It’s fancy stuff that we put in place to make sure
                            no one can gain access to your information except
                            you.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">How do I remove the small 
                            "Created by Invoice Ninja” image from the bottom of 
                            my invoices?
                    </a>
                    <div class="content">
                            <p>The amazingly affordable <a href="https://www.invoiceninja.com/plans">Pro</a> 
                            version of Invoice Ninja allows you to do this and oh so much more.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">I hear that there's a version of Invoice Ninja 
                    that I can install myself on my own servers? Where can I learn more about this?
                    </a>
                    <div class="content">
                            <p>The rumors are true! Full instructions are available <a href="http://hillelcoren.com/invoice-ninja/self-hosting/" target="_blank">here</a>.
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">I'm seeing the options to assign various 
                            statuses to my invoices, clients, credits and payments. What's 
                            the difference between "active," "archived" and "deleted"?
                    </a>
                    <div class="content">
                            <p>These three handy statuses for invoices, clients, credits and 
                            payments allow you to keep your own cash flow management as straightforward 
                            and accessible as possible from your Invoice Ninja dashboard. None of these 
                            statuses will actually purge any records from your account - even "deleted" can always 
                            be recovered at any point in the future. "Active" means the record will appear in the 
                            relevant queue of records. To stash a record away so it's still fully operational but no 
                            longer cluttering up your interface, simply set it to be "archived." To deactivate a record 
                            and render it inaccessible to your clients, mark it "deleted."
                        </p>
                    </div>
                </div>
                <div class="question">
                    <a class="expander" href="#">My question wasn’t covered
                        by any of the content on this FAQ page. How can I get
                        in touch with you?
                    </a>
                    <div class="content">
                        <p>Please email us at <a href=
                            "mailto:contact@invoiceninja.com">contact@invoiceninja.com</a>
                            with any questions or comments you have. We love
                            hearing from the people who use our app! We’ll do
                            our best to reply to your email within the business
                            day.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="contact-box">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="{{ asset('images/icon-faq.png') }}">
                            <h2>Did we miss something?</h2>
                        </div>
                        <div class="col-md-8 valign">
                            <p>Please email us at <a href=
                            "mailto:contact@invoiceninja.com" style=
                            "font-weight: bold">contact@invoiceninja.com</a> with
                            any questions or comments you have. We love hearing
                            from the people who use our app! We’ll do our best to
                            reply to your email within the business day.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@stop