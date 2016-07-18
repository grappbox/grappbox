using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace GrappBox.Ressources
{
    class MyDateTime
    {
        private DateTime _dateTime;
        public DateTime DateTimeAccess
        {
            get { return _dateTime; }
            set { _dateTime = value; }
        }
        public void AddDays(int days)
        {
            _dateTime = _dateTime.AddDays(days);
        }
        public void AddMonths(int months)
        {
            _dateTime = _dateTime.AddMonths(months);
        }
        public void AddYears(int years)
        {
            _dateTime = _dateTime.AddYears(years);
        }
        public MyDateTime()
        {
            DateTimeAccess = DateTime.Now;
        }
        public int Year { get { return _dateTime.Year; } }
        public int Month { get { return _dateTime.Month; } }
        public int Day { get { return _dateTime.Day; } }
        public override string ToString()
        {
            return _dateTime.ToString();
        }
    }
}