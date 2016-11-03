using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Windows.UI;
using Windows.UI.Xaml.Media;

namespace GrappBox
{
    internal static class Constants
    {
        #region Material Icons codes

        public const string DashboardSymbol = "\uE871";
        public const string CalendarSymbol = "\uE916";
        public const string TimelineSymbol = "\uE0BF";
        public const string BugtrackerSymbol = "\uE868";
        public const string TasksSymbol = "\uE85D";
        public const string WhiteboardSymbol = "\uE254";
        public const string ProjectSettingsSymbol = "\uE8B8";
        public const string UserSettingsSymbol = "\uE7FD";
        public const string LogoutSymbol = "\uE879";

        public const string AssignementSymbol = "\uE85D";

        #endregion Material Icons codes

        public static SolidColorBrush DefaultGray = new SolidColorBrush(Colors.DarkGray);

        #region Api calls string

        public const string DashboardGenericCall = "dashboard/projects";
        public const string DashboardTeamOccupationCall = "dashboard/occupation";
        public const string DashboardMeetingsCall = "dashboard/meetings";

        #endregion Api calls string
    }
}