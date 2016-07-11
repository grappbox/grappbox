using GrappBox.Model.Global;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    public class DateModel
    {
        public string date { get; set; }
        public int timezone_type { get; set; }
        public string timezone { get; set; }
        public static implicit operator DateTime(DateModel dm)
        {
            return DateTimeFormator.DateModelToDateTime(dm);
        }
    }
}
