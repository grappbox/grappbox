using System;
using System.Collections.Generic;
using System.Diagnostics;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.Helpers
{
    /// <summary>
    /// 
    /// </summary>
    class FormatDateTimeHelper
    {
        /// <summary>
        /// Extracts the time.
        /// </summary>
        /// <param name="datetime">The datetime.</param>
        /// <returns></returns>
        public static string ExtractTime(string datetime)
        {
            DateTime time;
            try
            {
                time = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return "Error";
            }
            return time.ToString("hh:mm");
        }

        /// <summary>
        /// Extracts the time.
        /// </summary>
        /// <param name="datetime">The datetime.</param>
        /// <param name="format">The format.</param>
        /// <returns></returns>
        public static string ExtractTime(string datetime, string format)
        {
            DateTime time;
            try
            {
                time = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return "Error";
            }
            return time.ToString(format);
        }

        /// <summary>
        /// Extracts the date.
        /// </summary>
        /// <param name="datetime">The datetime.</param>
        /// <returns></returns>
        public static string ExtractDate(string datetime)
        {
            DateTime date;
            try
            {
                date = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return "Error";
            }
            return date.ToString("dd/MM/yyyy");
        }

        /// <summary>
        /// Extracts the date.
        /// </summary>
        /// <param name="datetime">The datetime.</param>
        /// <param name="format">The format.</param>
        /// <returns></returns>
        public static string ExtractDate(string datetime, string format)
        {
            DateTime date;
            try
            {
                date = Convert.ToDateTime(datetime);
            }
            catch (Exception ex)
            {
                Debug.WriteLine(ex.Message);
                return "Error";
            }
            return date.ToString(format);
        }
    }
}
