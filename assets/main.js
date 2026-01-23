/* =========================================
   1. PREMIUM NAVBAR & MOBILE MENU LOGIC
   ========================================= */

// Select DOM elements
const btn = document.getElementById('mobileMenuBtn');
const menu = document.getElementById('mobileMenu');
const navbar = document.getElementById('navbar');
const bar1 = document.getElementById('bar1');
const bar2 = document.getElementById('bar2');
const bar3 = document.getElementById('bar3');
const mobileLinks = document.querySelectorAll('.mobile-link');

// --- SCROLL EFFECT: Transparent at top, Glass when scrolled ---
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        // Scrolled Down: Dark, Blurred, Border
        navbar.classList.add('bg-black/80', 'backdrop-blur-lg', 'border-white/10', 'shadow-lg');
        navbar.classList.remove('border-transparent');
    } else {
        // At Top: Transparent, No Border
        navbar.classList.remove('bg-black/80', 'backdrop-blur-lg', 'border-white/10', 'shadow-lg');
        navbar.classList.add('border-transparent');
    }
});

// --- MOBILE MENU TOGGLE ---
let isMenuOpen = false;

function toggleMenu() {
    isMenuOpen = !isMenuOpen;
    
    if (isMenuOpen) {
        // Open Menu
        menu.classList.remove('hidden');
        
        // Small delay to allow transition to kick in
        setTimeout(() => {
            menu.classList.remove('opacity-0');
        }, 10);
        
        document.body.style.overflow = 'hidden'; // Lock scroll

        // Hamburger Animation (Turn into X)
        // Adjust translation based on your specific hamburger icon height
        bar1.classList.add('rotate-45', 'translate-y-[6px]'); 
        bar2.classList.add('opacity-0');
        bar3.classList.add('-rotate-45', '-translate-y-[8px]');
    } else {
        // Close Menu
        menu.classList.add('opacity-0');
        
        // Wait for fade out animation before hiding element
        setTimeout(() => {
            menu.classList.add('hidden');
        }, 300);
        
        document.body.style.overflow = ''; // Unlock scroll

        // Reset Hamburger
        bar1.classList.remove('rotate-45', 'translate-y-[6px]');
        bar2.classList.remove('opacity-0');
        bar3.classList.remove('-rotate-45', '-translate-y-[8px]');
    }
}

// Event Listeners
if(btn) btn.addEventListener('click', toggleMenu);

mobileLinks.forEach(link => {
    link.addEventListener('click', () => {
        if(isMenuOpen) toggleMenu();
    });
});


/* =========================================
   2. BACKEND INTEGRATION: SCHEDULE
   ========================================= */
document.addEventListener('DOMContentLoaded', () => {
    loadSchedule();
});

async function loadSchedule() {
    const tableBody = document.getElementById('schedule-body');
    
    // Safety check: Does the table exist on this page?
    if (!tableBody) return;

    try {
        // Fetch data from PHP API
        const response = await fetch('api/get_schedule.php');
        const data = await response.json();

        // Check for DB errors sent from PHP
        if (data.error) {
            tableBody.innerHTML = `<tr><td colspan="7" class="p-6 text-red-500 text-center font-bold">Database Error: ${data.error}</td></tr>`;
            return;
        }

        // Clear "Connecting..." loading state
        tableBody.innerHTML = ''; 

        // Loop through the data and build rows
        data.forEach(slot => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-white/5 transition-colors group border-b border-white/5';

            row.innerHTML = `
                <td class="p-6 font-display text-2xl text-white border-r border-white/10 bg-black/50 group-hover:text-brand transition-colors whitespace-nowrap">
                    ${slot.time}
                </td>
                ${generateCell(slot.Monday)}
                ${generateCell(slot.Tuesday, true)}
                ${generateCell(slot.Wednesday)}
                ${generateCell(slot.Thursday, true)}
                ${generateCell(slot.Friday)}
                ${generateCell(slot.Saturday)}
                ${generateCell(slot.Sunday, true)}
            `;
            tableBody.appendChild(row);
        });

    } catch (error) {
        console.error('Fetch error:', error);
        tableBody.innerHTML = `<tr><td colspan="7" class="p-8 text-gray-500 text-center">
            Failed to load schedule.<br>
            <span class="text-xs text-red-500">Ensure XAMPP (Apache & MySQL) is running.</span>
        </td></tr>`;
    }
}

// Helper to style cells based on Class Type
function generateCell(classData, isDark = false) {
    const bgClass = isDark ? 'bg-white/5' : '';
    
    // If no class exists for this slot, return a dash
    if (!classData) {
        return `<td class="p-6 text-gray-700 text-center ${bgClass}">-</td>`;
    }

    // Default Colors
    let badgeClass = 'bg-white text-black'; 
    let dotClass = 'bg-green-500';

    // Color Logic based on Database ENUM ('Combat', 'Strength', 'Conditioning')
    if (classData.type === 'Combat') {
        badgeClass = 'bg-brand text-white'; // Red
        dotClass = 'bg-yellow-400';         // Yellow Dot
    } else if (classData.type === 'Strength') {
        badgeClass = 'bg-gray-700 text-white'; // Gray
        dotClass = 'bg-red-500';               // Red Dot
    } else if (classData.type === 'Conditioning') {
        badgeClass = 'bg-white text-black';    // White
        dotClass = 'bg-green-500';             // Green Dot
    }

    return `
        <td class="p-6 ${bgClass}">
            <span class="${badgeClass} px-3 py-1 rounded text-xs font-bold uppercase flex items-center gap-2 w-fit whitespace-nowrap shadow-lg">
                ${classData.title} <span class="w-1.5 h-1.5 rounded-full ${dotClass}"></span>
            </span>
        </td>
    `;
}