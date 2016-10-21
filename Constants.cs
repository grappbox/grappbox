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
        public const string DashboardSymbol = "\uE871";
        public const string CalendarSymbol = "\uE916";
        public const string TimelineSymbol = "\uE0BF";
        public const string BugtrackerSymbol = "\uE868";
        public const string TasksSymbol = "\uE03B";
        public const string WhiteboardSymbol = "\uE254";
        public const string ProjectSettingsSymbol = "\uE27C";
        public const string UserSettingsSymbol = "\uE7FD";
        public const string LogoutSymbol = "\uE879";

        public static SolidColorBrush DefaultGray = new SolidColorBrush(Colors.Black);
    }
}