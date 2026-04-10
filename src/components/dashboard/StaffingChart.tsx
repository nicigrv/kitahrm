"use client"

import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, Cell } from "recharts"

interface StaffingData {
  kitaName: string
  shortCode: string
  totalWeeklyHours: number
  activeEmployeeCount: number
}

interface StaffingChartProps {
  data: StaffingData[]
}

const COLORS = ["#3b82f6", "#8b5cf6", "#10b981", "#f59e0b", "#ef4444"]

// eslint-disable-next-line @typescript-eslint/no-explicit-any
function CustomTooltip({ active, payload, label }: any) {
  if (!active || !payload?.length) return null
  const d = payload[0]?.payload
  return (
    <div className="rounded-md border bg-white p-3 shadow text-sm">
      <p className="font-medium mb-1">{d?.fullName ?? label}</p>
      <p>{d?.stunden} Std./Woche</p>
      <p className="text-muted-foreground">{d?.mitarbeiter} Mitarbeiter</p>
    </div>
  )
}

export function StaffingChart({ data }: StaffingChartProps) {
  const chartData = data.map((d) => ({
    name: d.shortCode,
    fullName: d.kitaName,
    stunden: Math.round(d.totalWeeklyHours),
    mitarbeiter: d.activeEmployeeCount,
  }))

  return (
    <div className="h-64">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={chartData} margin={{ top: 5, right: 20, left: 0, bottom: 5 }}>
          <CartesianGrid strokeDasharray="3 3" stroke="#f0f0f0" />
          <XAxis dataKey="name" tick={{ fontSize: 12 }} />
          <YAxis tick={{ fontSize: 12 }} />
          <Tooltip content={<CustomTooltip />} />
          <Bar dataKey="stunden" radius={[4, 4, 0, 0]}>
            {chartData.map((_, index) => (
              <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  )
}
