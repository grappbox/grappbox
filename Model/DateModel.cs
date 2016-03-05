using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model
{
    class DateModel
    {
        public string date { get; set; }
        public int timezone_type { get; set; }
        public string timezone { get; set; }

        static private DateModel instance = null;

        static public DateModel GetInstance()
        {
            return instance;
        }
        public DateModel()
        {
            instance = this;
        }
    }
}
