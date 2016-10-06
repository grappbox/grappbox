﻿using Newtonsoft.Json.Converters;
using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Globalization;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox
{
    public static class DateTimeFormator
    {
        #region Pivate const
        private const string c_abridgedMonthName = "MMM";
        #endregion
        public static bool DateModelToDateTime(DateModel model, out DateTime dt)
        {
            try
            {
                dt = DateTime.Parse(model.date, CultureInfo.CurrentCulture);
            }
            catch(ArgumentException aEx)
            {
                Debug.WriteLine("Exception in DateModelToDateTime =>\nArgument Exception on Name {0} because of paramName {1}", aEx.Source, aEx.ParamName);
                dt = new DateTime();
                return false;
            }
            catch (FormatException fEx)
            {
                Debug.WriteLine("Exception in DateModelToDateTime =>\n{0}", fEx.Message);
                dt = new DateTime();
                return false;
            }
            return true;
        }
        public static DateModel DateTimeToDateModel(DateTime dt)
        {
            DateModel dm = new DateModel();
            dm.date = dt.ToString("yyyy-MM-dd HH:mm:ss");
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