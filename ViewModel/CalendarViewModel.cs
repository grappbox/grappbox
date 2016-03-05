using GrappBox.Model;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.ViewModel
{
    class CalendarViewModel : ViewModelBase
    {
        private CalendarModel model;
        public CalendarViewModel()
        {
            model = new CalendarModel();
            Debug.WriteLine("ViewModel");
            DateTime day = new DateTime(2016, 02, 01);
            model.GetDayPlanning(day);
        }
    }
}
