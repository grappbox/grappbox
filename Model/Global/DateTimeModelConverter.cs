using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Global
{
    public static class DateTimeModelConverter
    {
        public static DateTime DateModelToDateTime(DateModel model)
        {
            DateTime dt = DateTime.Parse(model.date, CultureInfo.CurrentCulture);
            Debug.WriteLine(dt.ToString(CultureInfo.CurrentCulture));
            return dt;
        }
        public static DateModel DateTimeToDateModel(DateTime dt)
        {
            DateModel dm = new DateModel();
            dm.date = dt.ToString("yyyy-MM-dd HH-mm-ss");
            dm.timezone_type = 3;
            dm.timezone = "Europe / Paris";
            Debug.WriteLine(dm.date);
            return dm;
        }
    }
}
