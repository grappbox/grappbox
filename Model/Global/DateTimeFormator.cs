using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Model.Global
{
    public static class DateTimeFormator
    {
        #region Private Statics
        private static readonly string[] _daysList = { "1", "2", "3", "4", "5", "6", "7", "8", "9", "10",
            "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31" };
        #endregion
        #region Pivate const
        private const string c_abridgedMonthName = "MMM";
        #endregion
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
        public static string GetMonthName(DateTime dt)
        {
            return dt == null ? dt.ToString(c_abridgedMonthName) : null;
        }
        public static string GetMonthName(DateTime dt, int month)
        {
            DateTime tmp = new DateTime(dt.Year, month, 1);
            return tmp.ToString(c_abridgedMonthName);
        }
        public static int GetDaysInMonth(DateTime dt)
        {
            return DateTime.DaysInMonth(dt.Year, dt.Month);
        }
        public static IEnumerable<string> GetDayList(DateTime dt, int month)
        {
            DateTime tmp = new DateTime(dt.Year, month, 1);
            return _daysList.Take(GetDaysInMonth(tmp));
        }
    }
}