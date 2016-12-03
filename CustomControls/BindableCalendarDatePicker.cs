using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI.Xaml;
using Windows.UI.Xaml.Controls;

namespace Grappbox.CustomControls
{
    class BindableCalendarDatePicker : CalendarDatePicker
    {
        public static readonly DependencyProperty DateBindProperty = DependencyProperty.Register(
            "DateBind", typeof(DateTime), typeof(BindableCalendarDatePicker), null);
        public DateTime DateBind
        {
            get
            {
               return  (DateTime)GetValue(DateBindProperty);
            }
            set
            {
                SetValue(DateBindProperty, value);
                this.Date = new DateTimeOffset(value);
            }
        }
    }
}
