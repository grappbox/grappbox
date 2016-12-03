using Grappbox.Helpers;
using Grappbox.Model;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Grappbox.ViewModel
{
    public class EventViewModel : Event
    {
        public bool IsSelected { get; set; } = false;

        public string BeginTime
        {
            get
            {
                return FormatDateTimeHelper.ExtractTime(BeginDate);
            }
        }

        public string EndTime
        {
            get
            {
                return FormatDateTimeHelper.ExtractTime(EndDate);
            }
        }
        public DateTime BeginDateTime
        {
            get
            {
                return DateTime.Parse(this.BeginDate);
            }
        }

        public DateTime EndDateTime
        {
            get
            {
                return DateTime.Parse(this.EndDate);
            }
        }
    }
}
