<div>
    <nav>
        <div class="container mx-auto px-4 py-6 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-bold text-pink-500">NailArt Booking</a>
            <ul class="flex space-x-6">
                <li><a href="{{ route('home') }}" class="text-gray-700 hover:text-pink-500 transition duration-300">Home</a></li>
                <li><a href="{{ route('naillist') }}" class="text-gray-700 hover:text-pink-500 transition duration-300">Nail List</a></li>
                <li><a href="{{ route('pricelist') }}" class="text-gray-700 hover:text-pink-500 transition duration-300">Price List</a></li>
                <li><a href="{{ route('schedule') }}" class="text-gray-700 hover:text-pink-500 transition duration-300">Schedule</a></li>
            </ul>
        </div>
    </nav>
</div>
